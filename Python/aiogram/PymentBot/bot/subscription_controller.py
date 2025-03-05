import logging
import asyncio
import config
from aiogram import Bot
import db
from aiogram.types import (Message, ReplyKeyboardMarkup, KeyboardButton,
                           InlineKeyboardMarkup, InlineKeyboardButton, CallbackQuery)

async def notify_users(bot: Bot):
    """Фоновая задача: уведомляет пользователей за 3 дня до окончания подписки."""
    while True:
        logging.info("🔍 Проверка подписок на истечение...")

        keyboard = InlineKeyboardMarkup(
            inline_keyboard=[
                [InlineKeyboardButton(text="💸 Оформить подписку", callback_data="sub_s1")],
                [InlineKeyboardButton(text="💬 Задать вопрос", callback_data="Contact_the_manager")],
                [InlineKeyboardButton(text="❓ FAQ", callback_data="FAQ")]
            ]
        )

        users = await db.get_users_by_subscription_status(days=3)
        for user in users:
            try:
                await bot.send_message(
                    user["user_id"],
                    f"⚠️ Ваша подписка истекает {user["subscription_end"].strftime('%Y-%m-%d %H:%M')}!\n"
                    "Продлите её заранее, чтобы не потерять доступ к каналу.", reply_markup=keyboard
                )
                logging.info(f"📢 Уведомление отправлено пользователю {user["user_id"]}.")
            except Exception as e:
                logging.error(f"❌ Ошибка при отправке уведомления {user["user_id"]}: {e}")

        await asyncio.sleep(86400)  # Ждём 24 часа перед следующей проверкой


async def remove_expired_users(bot: Bot):
    """Фоновая задача: удаляет пользователей с истекшей подпиской."""
    while True:
        logging.info("🔍 Проверка пользователей с истекшей подпиской...")

        users = await db.get_users_by_subscription_status(expired=True)

        for user in users:
            try:
                user_id = int(user["user_id"])  # Преобразуем в int

                logging.info(f"⏳ Удаление пользователя {user_id}...")

                await bot.ban_chat_member(config.CHANNEL_ID, user_id)
                await bot.unban_chat_member(config.CHANNEL_ID, user_id)  # Чтобы мог вернуться

                await bot.ban_chat_member(config.CHANNEL2_ID, user_id)
                await bot.unban_chat_member(config.CHANNEL2_ID, user_id)  # Чтобы мог вернуться

                await db.deactivate_user(user_id)

                await bot.send_message(
                    user_id,
                    "❗ Ваша подписка истекла."
                )

                logging.info(f"✅ Пользователь {user_id} удалён из канала.")

            except ValueError:
                logging.error(f"🚨 Ошибка преобразования user_id: {user}")  # Логируем проблемный user_id

            except Exception as e:
                logging.error(f"⚠️ Ошибка при удалении пользователя {user_id}: {e}")

        await asyncio.sleep(86400)  # Ждём 12 часа перед следующей проверкой