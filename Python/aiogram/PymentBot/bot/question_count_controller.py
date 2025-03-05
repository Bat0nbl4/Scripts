import asyncio
import datetime
import db

async def reset_questions_daily():
    """Обновляет количество доступных вопросов у всех пользователей"""
    while True:
        now = datetime.datetime.utcnow()
        next_reset = (now + datetime.timedelta(days=1)).replace(hour=0, minute=0, second=0, microsecond=0)
        sleep_time = (next_reset - now).total_seconds()

        await asyncio.sleep(sleep_time)
        await db.reset_questions()
        print("✅ Вопросы пользователей обновлены до 2-х.")