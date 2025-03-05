import logging
import asyncio

import config
import question_count_controller
import subscription_controller as sc
import keyboards
import str_helper

from aiogram import Bot, Dispatcher, F
from aiogram.types import (Message, InlineKeyboardMarkup, InlineKeyboardButton, CallbackQuery)
from aiogram.exceptions import TelegramBadRequest
import db
from aiogram.fsm.context import FSMContext
from aiogram.fsm.state import State, StatesGroup
from aiogram import types

logging.basicConfig(level=logging.INFO)

bot = Bot(token=config.TOKEN)
dp = Dispatcher()

pending_hashes = {}  # Временное хранилище перед подтверждением
pending_rejections = {}
rejecting_admins = {}  # Храним админов, которые вводят комментарий
pending_questions = {}  # Временное хранилище вопросов

# Словарь, где мы будем временно хранить соответствие short_key ↔ hash_code
pending_payments = {}  # { "ABC123": "полный_хеш_транзакции" }

class QuestionState(StatesGroup):
    waiting_for_question = State()  # Состояние ожидания вопроса

# Определяем состояние FSM для ожидания комментария
class RejectPaymentState(StatesGroup):
    waiting_for_comment = State()

class HashState(StatesGroup):
    waiting_for_hash = State()


async def set_bot_commands(bot: Bot):
    commands = [
        types.BotCommand(command="start", description="🏠 Главное меню"),
    ]
    await bot.set_my_commands(commands, scope=types.BotCommandScopeDefault())

@dp.message(F.text.lower() == "/start")
async def start_command(message: Message, bot: Bot):
    user_id = message.from_user.id
    username = message.from_user.first_name or "Неизвестный"

    # Добавляем пользователя в БД, если его нет
    await db.add_user_if_not_exists(user_id, username)

    await start(user_id, bot)

async def start(user_id: int, bot: Bot):
    """Команда /start: добавляет пользователя в БД и предлагает выбрать дальнейшее действие"""
    try:
        del rejecting_admins[user_id]
    except KeyError:
        pass
    try:
        del pending_questions[user_id]
    except KeyError:
        pass
    try:
        del pending_payments[user_id]
    except KeyError:
        pass

    await bot.send_message(user_id, config.HELLO_MESSAGE, reply_markup = await keyboards.main_menu(user_id))

@dp.callback_query(F.data == "MENU")
async def main_menu(query: CallbackQuery, state: FSMContext, bot: Bot):
    user_id = query.from_user.id

    await state.clear()
    await start(user_id, bot)

@dp.callback_query(F.data == "LIST")
async def ask_question_callback(query: CallbackQuery, bot: Bot):
    user_id = query.from_user.id
    if user_id not in db.admins:
        await query.answer("❌ Вы не являетесь администратором!")
        return
    users = await db.get_all_users()
    if len(users) == 0:
        await bot.send_message(user_id, str_helper.escape_md_v2("Список пользователей пуст :("), parse_mode="MarkdownV2")
        return

    msg = ""
    i = 1
    for user in users:
        msg += f"[{str_helper.escape_md_v2(user["username"])}](tg://user?id={user["user_id"]}) : {str_helper.escape_md_v2(str(user["remaining_days"]))} Дней\n"
        i += 1
        if i >= 25:
            await bot.send_message(user_id, msg, parse_mode="MarkdownV2")
            msg = ""
            i = 0

    if i < 25:
        await bot.send_message(user_id, msg, parse_mode="MarkdownV2")

    return

@dp.callback_query(F.data == "Contact_the_manager")
async def ask_question_callback(query: CallbackQuery, state: FSMContext):
    """Начинает процесс запроса вопроса у пользователя"""
    user_id = query.from_user.id

    # Получаем количество оставшихся вопросов
    remaining_questions = await db.get_remaining_questions(user_id)

    if remaining_questions <= 0:
        await query.message.answer("❌ У вас закончились вопросы на сегодня. Попробуйте снова завтра.")
        return

    keyboard = InlineKeyboardMarkup(
        inline_keyboard=[
            [InlineKeyboardButton(text="🛑 Я передумал задавать вопрос", callback_data="MENU")],
        ]
    )

    # Сообщаем пользователю, что у него 2 вопроса в день
    await query.message.answer(str_helper.escape_md_v2("📝 Вы можете задать 2 вопроса в день.\n\nВведите ваш вопрос:"), reply_markup=keyboard, parse_mode="MarkdownV2")

    # Устанавливаем состояние ожидания вопроса
    await state.set_state(QuestionState.waiting_for_question)

@dp.callback_query(F.data == "confirm_question")
async def confirm_question_callback(query: CallbackQuery, bot: Bot, state: FSMContext):
    """Подтверждает отправку вопроса и рассылает администраторам"""
    user_id = query.from_user.id

    if user_id not in pending_questions:
        await query.answer("⚠ Вопрос не найден. Попробуйте снова.", show_alert=True)
        return

    question_text = pending_questions.pop(user_id)

    # Ссылка на чат с пользователем
    user_chat_link = f"[Перейти в чат](tg://user?id={user_id})"

    # Отправляем сообщение всем администраторам
    for admin_id in db.admins:  # Используем admins (глобальный dict с ID админов)
        try:
            await bot.send_message(
                admin_id,
                str_helper.escape_md_v2(f"📩 Новый вопрос от пользователя\n"
                f"🔹 Вопрос: {str_helper.escape_md_v2(question_text)}\n") +
                f"👤 Пользователь: {user_chat_link}",
                parse_mode="MarkdownV2"
            )
        except Exception as e:
            logging.error(f"❌ Ошибка отправки вопроса админу {admin_id}: {e}")
            continue

    # Уменьшаем количество доступных вопросов
    await db.decrement_questions(user_id)
    await query.message.edit_text("✅ Ваш вопрос отправлен администраторам. Ожидайте ответа.", reply_markup=await keyboards.to_main_menu())
    await state.clear()

@dp.callback_query(F.data == "cancel_question")
async def cancel_question_callback(query: CallbackQuery):
    """Отмена отправки вопроса"""

    keyboard = InlineKeyboardMarkup(
        inline_keyboard=[
            [InlineKeyboardButton(text="🛑 Я передумал задавать вопрос", callback_data="MENU")],
        ]
    )

    await query.message.edit_text("🔹 Предыдущий вопрос отменён. Введите новый вопрос.", reply_markup=keyboard)

@dp.message(QuestionState.waiting_for_question)
async def receive_question(message: Message):
    """Получает вопрос от пользователя и предлагает его подтвердить"""
    user_id = message.from_user.id
    question_text = message.text.strip()

    if len(question_text) < 5:
        await message.answer("❌ Вопрос слишком короткий. Введите более развернутый вопрос.")
        return

    # Сохраняем вопрос в памяти
    pending_questions[user_id] = question_text

    keyboard = InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text="✅ Отправить", callback_data="confirm_question")],
        [InlineKeyboardButton(text="❌ Отменить", callback_data="cancel_question")]
    ])

    await message.answer(
        f"🔹 Ваш вопрос:\n{str_helper.escape_md_v2(question_text)}\n\nВы уверены, что хотите его отправить?",
        parse_mode="MarkdownV2",
        reply_markup=keyboard
    )

@dp.callback_query(F.data == "FAQ")
async def select_pricing_plan(query: CallbackQuery, bot: Bot):
    """Пользователь нажал оформить подписку"""
    user_id = query.from_user.id

    message = ("❕ Этот раздел находиться в разработке!\n\n"
               '👨‍💼 Вы можете связаться с нашим менеджером и задать ему свой вопрос с помощью опции "💬 Задать вопрос" в главном меню.\n\n'
               "📝 Возможно, потом именно ваш вопрос будет в этом разделе!")

    await bot.send_message(user_id, message, reply_markup=await keyboards.to_main_menu())

@dp.callback_query(F.data == "sub_s1")
async def select_pricing_plan(query: CallbackQuery, bot: Bot):
    """Пользователь нажал оформить подписку"""
    user_id = query.from_user.id
    buttons = []
    for plan, desc in config.PRICING_PLANS.items():
        buttons.append([InlineKeyboardButton(text=f"{plan} - {desc}", callback_data=f"sub_s2_{plan}")])
    buttons.append([InlineKeyboardButton(text=f"◀️ Назад, в меню", callback_data="MENU")])
    keyboard = InlineKeyboardMarkup(inline_keyboard = buttons)

    await bot.send_message(user_id, "📋 Выберите тариф", reply_markup=keyboard)

@dp.callback_query(F.data.startswith("sub_s2_"))
async def select_network_callback(query: CallbackQuery, bot: Bot):
    """Пользователь выбрал тариф"""
    user_id = query.from_user.id
    plan = query.data.split("_")[2]

    # Сохраняем сеть в pending_hashes
    pending_hashes[user_id] = {"plan": plan, "network": None}

    buttons = []
    for key in config.NETWORKS.keys():
        buttons.append([InlineKeyboardButton(text=f"💰 {key}", callback_data=f"select-network_{key}")])
    buttons.append([InlineKeyboardButton(text=f"◀️ Назад, к выбору тарифа", callback_data="sub_s1")])

    keyboard = InlineKeyboardMarkup(inline_keyboard=buttons)

    message = (f"✅ Вы выбрали тарифный план: {plan} - {config.PRICING_PLANS[plan]}.\n\n"
               f"🔹 Выберите сеть, через которую хотите оплатить подписку:")

    await bot.send_message(user_id, message, reply_markup=keyboard)

@dp.callback_query(F.data.startswith("select-network_"))
async def select_network_callback(query: CallbackQuery, bot: Bot, state: FSMContext):
    """Обрабатывает выбор сети пользователем"""
    user_id = query.from_user.id
    network = query.data.split("_")[1]  # Извлекаем название сети (TRC20 или APROS)

    # Сохраняем сеть в pending_hashes
    try:
        pending_hashes[user_id]["network"] = network
    except KeyError:
        await query.answer("⚠️ Сначала выберите тариф!")
        return

    message = (f"✅ Тарифный план: {pending_hashes[user_id]["plan"]} - {config.PRICING_PLANS[pending_hashes[user_id]["plan"]]}. \n"
               f"✅ Сеть для оплаты: {pending_hashes[user_id]["network"]}.\n\n"
               f"🔗 Адрес кошелька для оплаты:\n {config.NETWORKS[pending_hashes[user_id]["network"]]}\n\n"
               f"🔹 После оплаты отправьте мне хеш код транзакции сообщением. Если отправляете внутренним переводом, то id-ордера.")

    keyboard = InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text=f"◀️ Назад, к выбору сети", callback_data=f"sub_s2_{pending_hashes[user_id]["plan"]}")],
        [InlineKeyboardButton(text="📱 Главное меню", callback_data="MENU")]
    ])

    await state.set_state(HashState.waiting_for_hash)
    await bot.send_message(user_id, message, reply_markup=keyboard)

@dp.callback_query(F.data.startswith("CP_"))
async def confirm_payment_callback(query: CallbackQuery, bot: Bot):
    """Обрабатывает подтверждение платежа администратором"""

    short_key = query.data.split("_")[1]
    if not short_key:
        await query.answer("⚠ Платёж уже обработан или не найден.", show_alert=True)
        return

    hash_code = await db.get_hash_by_short_key(short_key)
    if not hash_code:
        await query.answer("⚠ Платёж уже обработан или не найден.", show_alert=True)
        return

    admin_id = query.from_user.id

    # Проверяем, был ли платёж уже обработан
    payment_info = await db.get_payment_info(hash_code)
    if payment_info is None:
        return

    if payment_info and payment_info["status"] != "pending":
        admin_name = await db.get_admin_name(payment_info["processed_by"], bot)
        status = "❔ Неопределённое состояние"
        if payment_info["status"] == "confirmed":
            status = "✅ Платёж подтверждён!"
        elif payment_info["status"] == "rejected":
            status = "❌ Платёж отклонён!"
        comment = None
        if payment_info["comment"]:
            comment = f"💬Комментарий: {payment_info['comment']}"
        processed_text = (
            "Этот платёж уже обработан.\n\n"
            f"🔗 Хеш: {hash_code}\n"
            f"{status}\n"
            f"👤 Обработал: {admin_name}\n"
            f"{comment}"
        )
        await query.message.edit_text(str_helper.escape_md_v2(processed_text), parse_mode="MarkdownV2")
        return

    plan = int(payment_info["pricing_plan"].split(" ")[0])
    # Подтверждаем платёж
    result = await db.approve_payment(hash_code, admin_id, 30 * plan)
    if not result:
        await query.answer("⚠️ Ошибка: платёж не найден.", show_alert=True)
        return

    user_id, expiry_date = result

    invite_msg = ""
    user_is_active = await db.user_is_active(payment_info["user_id"])
    if not user_is_active or user_is_active[0] == 0:
        # Генерируем **одноразовую ссылку** на канал
        try:
            invite_link = await bot.create_chat_invite_link(
                chat_id=config.CHANNEL_ID,  # ID закрытого канала
                member_limit=1  # Ссылка срабатывает только для 1 пользователя
            )
            invite_url = invite_link.invite_link
        except TelegramBadRequest:
            invite_url = "Ошибка генерации ссылки. Обратитесь в поддержку."

        await db.update_invite_link(user_id, invite_url)

        invite_msg = f"\n\n📢 Ваша персональная ссылка на канал: [Нажмите сюда]({invite_url})"

    await db.activate_user(payment_info["user_id"])
    await bot.send_message(
        user_id,
        str_helper.escape_md_v2(f"🎉 Ваш платёж был подтверждён!\n"
                     f"ℹ️ Ваша подписка активна до {expiry_date.strftime('%Y-%m-%d %H:%M')}") +
                     invite_msg,
        parse_mode="MarkdownV2"
    )

    # Обновляем сообщение для администратора
    new_text = (
        "✅ Платёж подтверждён!\n"
        f"🔗 Хеш: {hash_code}\n"
        f"🔹 Обработал: {query.from_user.username}"
    )

    try:
        await query.message.edit_text(str_helper.escape_md_v2(new_text), parse_mode="MarkdownV2")
    except TelegramBadRequest:
        await query.answer("⚠️ Не удалось обновить сообщение. Возможно, оно было удалено.")

    await query.answer("✅ Платёж успешно подтверждён.", show_alert=True)

@dp.callback_query(F.data.startswith("reject_"))
async def reject_payment_callback(query: CallbackQuery, state: FSMContext, bot: Bot):
    """Обработчик отклонения платежа"""
    admin_id = query.from_user.id
    short_key = query.data.split("_")[1]
    if not short_key:
        await query.answer("⚠ Платёж уже обработан или не найден.", show_alert=True)
        return

    hash_code = await db.get_hash_by_short_key(short_key)
    if not hash_code:
        await query.answer("⚠ Платёж уже обработан или не найден.", show_alert=True)
        return

    payment_info = await db.get_payment_info(hash_code)
    if payment_info is None:
        return

    if payment_info and payment_info["status"] != "pending":
        admin_name = await db.get_admin_name(payment_info["processed_by"], bot)
        status = "❔ Неопределённое состояние"
        if payment_info["status"] == "confirmed":
            status = "✅ Платёж подтверждён!"
        elif payment_info["status"] == "rejected":
            status = "❌ Платёж отклонён!"
        comment = None
        if payment_info["comment"]:
            comment = f"💬Комментарий: {payment_info['comment']}"
        processed_text = (
            f"🔗 Хеш: {hash_code}\n"
            f"{status}\n"
            f"🔹 Обработал: {admin_name}\n"
            f"{comment}"
        )
        await query.message.edit_text(str_helper.escape_md_v2(processed_text), parse_mode="MarkdownV2")
        return

    keyboard = InlineKeyboardMarkup(
        inline_keyboard=[
            [InlineKeyboardButton(text="🛑 Отменить ввод.", callback_data="BACI")]
        ]
    )

    # Сообщаем администратору, что нужно ввести комментарий
    await query.message.answer("✍️ Введите комментарий для отказа:", reply_markup=keyboard)

    # Запускаем процесс отклонения
    rejecting_admins[admin_id] = short_key

    # Устанавливаем состояние ожидания комментария
    await state.set_state(RejectPaymentState.waiting_for_comment)

@dp.message(RejectPaymentState.waiting_for_comment)
async def receive_reject_comment(message: Message):
    """Принимает комментарий администратора для отклонённого платежа и просит подтвердить"""
    admin_id = message.from_user.id

    if admin_id not in rejecting_admins:
        await message.answer("⚠️ Вы не отклоняете платёж.")
        return

    short_key = rejecting_admins[admin_id]
    comment = message.text.strip()

    # Сохраняем комментарий во временный кеш
    pending_rejections[admin_id] = comment

    # Формируем клавиатуру подтверждения
    keyboard = InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text="✅ Подтвердить", callback_data=f"ACR_{short_key}")],
        [InlineKeyboardButton(text="❌ Изменить", callback_data=f"AER_{short_key}")],
        [InlineKeyboardButton(text="🛑 Отменить ввод.", callback_data="BACI")]
    ])

    await message.answer(
        f"💬 Вы ввели следующий комментарий:\n\n{str_helper.escape_md_v2(comment)}\n\n"
        "Вы уверены, что хотите отклонить платёж с этим комментарием?",
        parse_mode="MarkdownV2",
        reply_markup=keyboard
    )

@dp.callback_query(F.data.startswith("ACR_"))
async def confirm_reject_callback(query: CallbackQuery, state: FSMContext, bot: Bot):
    """Обрабатывает подтверждение отклонения платежа"""
    admin_id = query.from_user.id
    short_key = query.data.split("_")[1]
    hash_code = await db.get_hash_by_short_key(short_key)

    if admin_id not in pending_rejections:
        await query.answer("Ошибка: комментарий не найден.")
        return

    comment = pending_rejections.pop(admin_id)

    # Получаем информацию о платеже
    payment_info = await db.get_payment_info(hash_code)
    if payment_info is None:
        await query.answer("⚠️ Платёж не найден.", show_alert=True)
        return

    user_id = payment_info["user_id"]

    # Уведомляем пользователя о том, что его платёж отклонён
    try:
        await bot.send_message(
            user_id,
            str_helper.escape_md_v2(f"❌ Ваш платёж был отклонён.\n💬 Комментарий администратора: {comment}"),
            parse_mode="MarkdownV2",
            reply_markup=await keyboards.to_main_menu()
        )

    except TelegramBadRequest:
        logging.error(f"❌ Ошибка отправки уведомления пользователю {user_id}")

    # Обновляем статус платежа в БД
    await db.reject_payment(hash_code, admin_id, comment)

    # Удаляем администратора из списка отклоняющих платеж
    del rejecting_admins[admin_id]

    await query.answer("✅ Платёж успешно отклонён.", show_alert=True)

    await state.clear()

@dp.callback_query(F.data.startswith("AER_"))
async def edit_reject_callback(query: CallbackQuery, state: FSMContext):
    """Позволяет администратору изменить комментарий перед отклонением"""
    admin_id = query.from_user.id

    if admin_id not in pending_rejections:
        await query.answer("Ошибка: комментарий не найден.")
        return

    keyboard = InlineKeyboardMarkup(
        inline_keyboard=[
            [InlineKeyboardButton(text="🛑 Отменить ввод.", callback_data="BACI")]
        ]
    )

    await query.message.answer("✍️ Введите новый комментарий для отклонения платежа.", keyboard=keyboard)
    await state.set_state(RejectPaymentState.waiting_for_comment)

@dp.message(HashState.waiting_for_hash)
async def input_hash_callback(message: Message, state: FSMContext):
    """Обрабатывает ввод пользователя: хеш."""
    user_id = message.from_user.id
    text = message.text.strip()

    if user_id in pending_hashes and "network" in pending_hashes[user_id]:
        network = pending_hashes[user_id]["network"]

        # Проверяем хеш
        if not str_helper.is_hash(text):
            keyboard = InlineKeyboardMarkup(inline_keyboard=[
                [InlineKeyboardButton(text=f"◀️ Назад, к выбору сети", callback_data=f"sub_s2_{pending_hashes[user_id]["plan"]}")],
                [InlineKeyboardButton(text="📱 Главное меню", callback_data="MENU")]
            ])
            msg = str_helper.escape_md_v2(
                "⚠️ Пожалуйста, пришлите правильный txID (хеш) вашей транзакции или id-ордера, если отправляете внутренни переводом!\n\nℹ️ Вы можете найти его в деталях перевода на бирже, с которой оплачивали или на официальном сайте ")
            if network == "BEP20":
                msg += "[www\\.bscscan\\.com](https://www.bscscan.com/)"
            elif network == "TRC20":
                msg += "[www\\.tronscan\\.org](https://www.tronscan.org/)"
            await message.answer(msg, parse_mode="MarkdownV2", reply_markup=keyboard)
            return

        pending_hashes[user_id]["hash"] = text  # Сохраняем хеш
        logging.info(f"🔗 Хеш сохранён для user_id {user_id}, сеть: {network}")

        try:
            hashes = list(await db.get_hashes())
            if (text,) in hashes:
                await message.answer(str_helper.escape_md_v2("Этот хеш уже кто использовал! Введите новый хеш!"), parse_mode="MarkdownV2")
                return
        except TypeError:
            pass

        keyboard = InlineKeyboardMarkup(
            inline_keyboard=[
                [InlineKeyboardButton(text="✅ Да, отправить", callback_data=f"CH_{user_id}")],
                [InlineKeyboardButton(text="❌ Нет, изменить", callback_data="DH")],
                [InlineKeyboardButton(text=f"◀️ Назад, к выбору сети", callback_data=f"sub_s2_{pending_hashes[user_id]["plan"]}")],
                [InlineKeyboardButton(text="📱 Главное меню", callback_data="MENU")]
            ]
        )

        await message.answer(f"Вы выбрали сеть: {network}\n"
                             f"🔗 Ваш хеш: {str_helper.escape_md_v2(text)}\n\n"
                             "Вы уверены, что хотите его отправить?",
                             parse_mode="MarkdownV2",
                             reply_markup=keyboard)
        await state.clear()
        return

@dp.message()
async def handle_user_input(message: Message):
    """Обрабатывает ввод пользователя: хеш транзакции или комментарий администратора."""
    user_id = message.from_user.id
    if user_id == config.CHANNEL2_ID:
        return

@dp.callback_query(F.data.startswith("CH_"))
async def confirm_hash_callback(query: CallbackQuery):
    """Обрабатывает подтверждение хеша пользователем"""
    user_id = int(query.data.split("_", 1)[1])  # Берём user_id

    if user_id not in pending_hashes or "hash" not in pending_hashes[user_id]:
        await query.answer("❌ Ошибка: нет ожидающего подтверждения хеша.")
        return

    network = pending_hashes[user_id]["network"]
    pricing_plan = pending_hashes[user_id]["plan"]
    full_hash = pending_hashes[user_id]["hash"]

    await db.save_payment_request(user_id, full_hash, network, pricing_plan, config.PRICING_PLANS[pricing_plan])

    final_message = str_helper.escape_md_v2("✅ Хеш отправлен на проверку администраторам.")

    await query.message.edit_text(final_message, parse_mode="MarkdownV2", reply_markup=await keyboards.to_main_menu())

    msg_start = str_helper.escape_md_v2("🔔 Новый платёж!\n")

    # Отправляем администраторам
    user = await bot.get_chat(user_id)
    user_name = user.full_name
    user_link = f"👤 Пользователь: [{user_name}](tg://user?id={user_id}), ID:{user_id}\n"

    msg = str_helper.escape_md_v2 (
        f"🔗 Хеш: {full_hash}\n"
        f"🌐 Сеть: {network}\n"
        f"📋 Тариф: {pricing_plan}\n"
        f"💸 К оплате: {config.PRICING_PLANS[pricing_plan]}\n"
        f"⚙️ Подтвердить или отклонить?"
    )

    for admin_id in db.admins:
        await bot.send_message(
            admin_id, msg_start + user_link + msg,
            parse_mode="MarkdownV2",
            reply_markup=await keyboards.generate_admin_buttons(full_hash)
        )

    del pending_hashes[user_id]  # Очищаем временные данные

@dp.callback_query(F.data == "DH")
async def cancel_hash_callback(query: CallbackQuery, state: FSMContext):
    """Отмена ввода хеша"""
    user_id = query.from_user.id
    del pending_hashes[user_id]["hash"]

    keyboard = InlineKeyboardMarkup(
        inline_keyboard=[
            [InlineKeyboardButton(text=f"◀️ Назад, к выбору сети", callback_data=f"sub_s2_{pending_hashes[user_id]["plan"]}")],
            [InlineKeyboardButton(text="📱 Главное меню", callback_data="MENU")]
        ]
    )

    await query.message.edit_text("❌ Ввод хеша отменён. Вы можете отправить новый хеш.", reply_markup=keyboard)
    await state.set_state(HashState.waiting_for_hash)

@dp.callback_query(F.data == "ADH")
async def cancel_comment_callback(query: CallbackQuery):
    """Отмена ввода комментария / ожидание нового"""

    keyboard = InlineKeyboardMarkup(
        inline_keyboard=[
            [InlineKeyboardButton(text="🛑 Отменить ввод.", callback_data="BACI")]
        ]
    )

    await query.message.edit_text("❌ Ввод комментария отменён. Вы можете отправить новый комментарий.", reply_markup=keyboard)

@dp.callback_query(F.data == "BACI")
async def break_comment_input(query: CallbackQuery, state: FSMContext):
    """Отмена ввода комментария"""
    user_id = query.from_user.id
    del rejecting_admins[user_id]
    await state.clear()
    await query.message.edit_text("🛑 Ввод комментария отменён.", reply_markup=await keyboards.to_main_menu())


async def main():
    logging.info("🚀 Бот запускается...")
    await db.create_db_pool()  # Создаём пул соединений
    await db.create_tables()  # Используем глобальный пул
    await db.load_admins()

    # Запускаем фоновые задачи
    asyncio.create_task(sc.notify_users(bot))
    asyncio.create_task(sc.remove_expired_users(bot))
    asyncio.create_task(question_count_controller.reset_questions_daily())

    try:
        await set_bot_commands(bot)  # Команды для пользователей
        await dp.start_polling(bot)
    finally:
        await db.close_db_pool()  # Закрываем пул при остановке бота

if __name__ == "__main__":
    asyncio.run(main())