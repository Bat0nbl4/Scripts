from aiogram.types import (Message, ReplyKeyboardMarkup, KeyboardButton,
                           InlineKeyboardMarkup, InlineKeyboardButton, CallbackQuery)
import str_helper, db

async def generate_admin_buttons(hash_code: str):
    """Генерирует клавиатуру для администратора и сохраняет short_key в БД"""

    # Проверяем, есть ли уже short_key для этого хеша
    existing_short_key = await db.get_short_key(hash_code)

    if existing_short_key:
        short_key = existing_short_key  # Если уже есть, используем его
    else:
        short_key = str_helper.generate_short_key()  # Генерируем новый
        await db.save_short_key(hash_code, short_key)  # Сохраняем в БД

    return InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text="✅ Подтвердить", callback_data=f"CP_{short_key}")],
        [InlineKeyboardButton(text="❌ Отклонить", callback_data=f"reject_{short_key}")]
    ])

async def main_menu(user_id: int):
    buttons = [
        [InlineKeyboardButton(text="💸 Оформить подписку", callback_data="sub_s1")],
        [InlineKeyboardButton(text="💬 Задать вопрос", callback_data="Contact_the_manager")],
        [InlineKeyboardButton(text="❓ FAQ", callback_data="FAQ")]
    ]

    if user_id in db.admins:
        buttons.append([InlineKeyboardButton(text="👥 Список пользователй", callback_data="LIST")])

    return InlineKeyboardMarkup(inline_keyboard=buttons)

async def to_main_menu():
    buttons = [
        [InlineKeyboardButton(text="📱 Главное меню", callback_data="MENU")]
    ]

    return InlineKeyboardMarkup(inline_keyboard=buttons)