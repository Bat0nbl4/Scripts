import config
import aiomysql
import logging
from aiogram import Bot

db_conf = config.DB_CONFIG
db_pool = None  # Глобальная переменная для пула

async def create_db_pool():
    """Создаёт глобальный пул соединений с MySQL."""
    global db_pool
    if db_pool is None:
        logging.info("🛠️ Создаём пул соединений с БД...")
        try:
            db_pool = await aiomysql.create_pool(
                host=db_conf["host"],
                port=db_conf["port"],
                user=db_conf["user"],
                password=db_conf["password"],
                db=db_conf["database"],
                autocommit=True,
                charset='utf8',
                use_unicode=True,
                connect_timeout=15
            )
            logging.info("✅ Пул соединений успешно создан.")
        except Exception as e:
            logging.error(f"❌ Ошибка при создании пула соединений: {e}")
            raise
    else:
        logging.info("✅ Пул соединений уже существует.")

async def close_db_pool():
    """Закрывает глобальный пул соединений."""
    global db_pool
    if db_pool:
        db_pool.close()
        await db_pool.wait_closed()
        db_pool = None
async def ensure_db_pool():
    """Проверяет, инициализирован ли пул соединений с БД."""
    global db_pool
    if db_pool is None:
        raise RuntimeError("❌ Ошибка: Пул соединений с БД не инициализирован!")

async def tables_exist():
    """Проверяет, существуют ли таблицы в базе данных."""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor() as cur:
            await cur.execute("SHOW TABLES LIKE 'users'")
            users_table = await cur.fetchone()

            await cur.execute("SHOW TABLES LIKE 'payments'")
            payments_table = await cur.fetchone()

    return users_table and payments_table  # True, если обе таблицы существуют
async def create_tables():
    """Создаёт таблицы, если их нет, и обновляет структуру при необходимости."""
    await ensure_db_pool()
    if not await tables_exist():
        async with db_pool.acquire() as conn:
            async with conn.cursor() as cur:
                # Таблица пользователей
                await cur.execute("""
                    CREATE TABLE IF NOT EXISTS users (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id BIGINT UNIQUE NOT NULL,
                        username VARCHAR(255),
                        subscription_end DATETIME,
                        is_active TINYINT(1) DEFAULT 0,
                        invite_link TEXT,
                        questions_count INT DEFAULT 2,
                        status VARCHAR(10) DEFAULT NULL
                    )
                """)

                # Таблица платежей
                await cur.execute("""
                    CREATE TABLE IF NOT EXISTS payments (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id BIGINT NOT NULL,
                        network VARCHAR(10) NOT NULL,  -- TRC20 или APROS вместо ton_address
                        hash_code VARCHAR(255) NOT NULL,
                        short_key VARCHAR(12) DEFAULT NULL,
                        status ENUM('pending', 'confirmed', 'rejected') NOT NULL DEFAULT 'pending',
                        pricing_plan VARCHAR(32) NOT NULL,
                        that_time_price VARCHAR(32) NOT NULL,
                        comment TEXT DEFAULT NULL,
                        processed_by BIGINT DEFAULT NULL
                    )
                """)

                logging.info("⚠️ Таблицы созданы!")

    logging.info("✅ Таблицы готовы к работе.")

admins = set()  # Храним всех админов в множестве
async def load_admins():
    """Загружает список администраторов из БД"""
    global admins
    await ensure_db_pool()

    async with db_pool.acquire() as conn:
        async with conn.cursor() as cur:
            await cur.execute("SELECT user_id FROM users WHERE status = 'Admin'")
            result = await cur.fetchall()
            admins = {row[0] for row in result}  # Записываем ID админов в set

    logging.info(f"✅ Загружено {len(admins)} администраторов.")

async def reject_payment(hash_code: str, admin_id: int, comment: str):
    """Отклоняет платёж, добавляет комментарий и указывает администратора."""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor() as cur:
            await cur.execute("""
                UPDATE payments 
                SET status = 'rejected', processed_by = %s, comment = %s
                WHERE hash_code = %s
            """, (admin_id, comment, hash_code))

    logging.info(f"❌ Платёж с хешем {hash_code} был отклонён администратором {admin_id}. Комментарий: {comment}")
async def approve_payment(hash_code: str, admin_id: int, days:int = 30):
    """Подтверждает платёж и обновляет подписку пользователя"""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor() as cur:
            # Получаем user_id по хешу
            await cur.execute("SELECT user_id FROM payments WHERE hash_code = %s", (hash_code,))
            result = await cur.fetchone()
            if not result:
                return None  # Если платежа нет, выходим

            user_id = result[0]

            # Обновляем статус платежа
            await cur.execute(
                "UPDATE payments SET status = 'confirmed', processed_by = %s WHERE hash_code = %s",
                (admin_id, hash_code)
            )

            # Проверяем текущую подписку пользователя
            await cur.execute("SELECT subscription_end FROM users WHERE user_id = %s", (user_id,))
            user_result = await cur.fetchone()

            from datetime import datetime, timedelta

            new_expiry_date = datetime.utcnow() + timedelta(days=days)  # Подписка на 1 месяц

            if user_result and user_result[0]:  # Если подписка уже есть, продлеваем
                current_expiry = user_result[0]
                if current_expiry > datetime.utcnow():
                    new_expiry_date = current_expiry + timedelta(days=30)

            # Обновляем подписку в БД
            await cur.execute(
                "UPDATE users SET subscription_end = %s WHERE user_id = %s",
                (new_expiry_date, user_id)
            )

    return user_id, new_expiry_date


async def get_payment_info(hash_code: str):
    """Получает информацию о платеже по хешу."""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor(aiomysql.DictCursor) as cur:  # Используем DictCursor
            await cur.execute("""
                SELECT * FROM payments WHERE hash_code = %s
            """, (hash_code,))
            return await cur.fetchone()  # Теперь это словарь
async def save_short_key(hash_code: str, short_key: str):
    """Сохраняет короткий ID в базу данных"""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor() as cur:
            await cur.execute(
                "UPDATE payments SET short_key = %s WHERE hash_code = %s",
                (short_key, hash_code)
            )
async def get_short_key(hash_code: str):
    """Получает `short_key` по `hash_code`"""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor(aiomysql.DictCursor) as cur:
            await cur.execute(
                "SELECT short_key FROM payments WHERE hash_code = %s",
                (hash_code,)
            )
            result = await cur.fetchone()
            return result["short_key"] if result else None
async def get_hash_by_short_key(short_key: str):
    """Получает `hash_code` по `short_key`"""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor(aiomysql.DictCursor) as cur:
            await cur.execute(
                "SELECT hash_code FROM payments WHERE short_key = %s",
                (short_key,)
            )
            result = await cur.fetchone()
            return result["hash_code"] if result else None


async def get_payment_user(hash_code: str):
    """Возвращает user_id по хешу транзакции"""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor() as cur:
            await cur.execute("""
                SELECT user_id FROM payments WHERE hash_code = %s
            """, (hash_code,))
            return await cur.fetchone()
async def get_admin_name(user_id: int, bot: Bot):
    """Получает имя администратора по user_id через Telegram API"""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor(aiomysql.DictCursor) as cur:
            await cur.execute("SELECT username FROM users WHERE user_id = %s", (user_id,))
            result = await cur.fetchone()

    # Если username есть в БД, используем его
    if result and result["username"]:
        return result["username"]

    # Если нет, запрашиваем Telegram API
    try:
        user = await bot.get_chat(user_id)
        return user.first_name
    except Exception:
        return "Неизвестный администратор"


async def save_payment_request(user_id: int, hash_code: str, network: str, pricing_plan: str, price:str):
    """Сохраняет данные о платеже в БД"""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor() as cur:
            await cur.execute("""
                INSERT INTO payments (user_id, network, hash_code, pricing_plan, that_time_price, status)
                VALUES (%s, %s, %s, %s, %s, 'pending')
            """, (user_id, network, hash_code, pricing_plan, price))

async def add_user_if_not_exists(user_id: int, username: str):
    """Добавляет нового пользователя в БД, если он ещё не существует"""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor() as cur:
            # Проверяем, есть ли пользователь в БД
            await cur.execute("SELECT COUNT(*) FROM users WHERE user_id = %s", (user_id,))
            result = await cur.fetchone()

            if result[0] == 0:  # Если пользователя нет, добавляем его
                await cur.execute("""
                    INSERT INTO users (user_id, username, subscription_end, status)
                    VALUES (%s, %s, NULL, NULL)
                """, (user_id, username))
                logging.info(f"✅ Новый пользователь добавлен: {user_id} ({username})")
                return True
            return False

async def check_subscription(user_id: int):
    """Проверяет, активна ли подписка у пользователя."""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor() as cur:
            await cur.execute("""
                SELECT subscription_end FROM users WHERE user_id = %s AND is_active = TRUE
            """, (user_id,))
            result = await cur.fetchone()
            return result[0] if result else None

async def update_invite_link(user_id: int, invite_link: str):
    """Обновляет ссылку-приглашение пользователя."""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor() as cur:
            await cur.execute("""
                UPDATE users SET invite_link = %s WHERE user_id = %s
            """, (invite_link, user_id))

async def get_invite_link(user_id: int):
    """Возвращает ссылку-приглашение, если она есть"""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor() as cur:
            await cur.execute("""
                SELECT invite_link FROM users WHERE user_id = %s
            """, (user_id,))
            result = await cur.fetchone()
            return result[0] if result else None

async def get_hashes():
    """Возвращает ссылку-приглашение, если она есть"""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor() as cur:
            await cur.execute("""
                SELECT hash_code FROM payments
            """)
            result = await cur.fetchall()
            return result if result else None

async def get_users_waiting_for_payment():
    """Возвращает список пользователей, ожидающих оплаты."""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor(aiomysql.DictCursor) as cur:
            await cur.execute("""
                SELECT user_id, network, hash_code FROM payments
                WHERE status = 'pending'
            """)
            users = await cur.fetchall()
    return users

async def user_is_active(user_id: int):
    """Помечает пользователя как неактивного (он больше не в канале)."""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor() as cur:
            await cur.execute("""
                SELECT is_active FROM users WHERE user_id = %s
            """, (user_id,))
        return await cur.fetchone()

async def activate_user(user_id: int):
    """Помечает пользователя как неактивного (он больше не в канале)."""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor() as cur:
            await cur.execute("""
                UPDATE users SET is_active = TRUE WHERE user_id = %s
            """, (user_id,))

async def deactivate_user(user_id: int):
    """Помечает пользователя как неактивного (он больше не в канале)."""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor() as cur:
            await cur.execute("""
                UPDATE users SET is_active = FALSE WHERE user_id = %s
            """, (user_id,))

async def confirm_payment(hash_code: str, admin_id: int):
    """Подтверждает оплату, активирует подписку и обновляет статус платежа."""
    await ensure_db_pool()

    async with db_pool.acquire() as conn:
        async with conn.cursor() as cur:
            # Получаем user_id и network по хешу
            await cur.execute("""
                SELECT user_id, network FROM payments WHERE hash_code = %s
            """, (hash_code,))
            result = await cur.fetchone()

            if not result:
                logging.warning(f"⚠️ Платёж с хешем {hash_code} не найден!")
                return

            user_id, network = result

            # Продлеваем подписку на 1 месяц
            await cur.execute("""
                UPDATE users SET subscription_end = DATE_ADD(NOW(), INTERVAL 1 MONTH), is_active = TRUE
                WHERE user_id = %s
            """, (user_id,))

            # Обновляем статус платежа
            await cur.execute("""
                UPDATE payments SET status = 'confirmed', processed_by = %s WHERE hash_code = %s
            """, (admin_id, hash_code))

    logging.info(f"✅ Подписка пользователя {user_id} продлена после подтверждения платежа.")

async def get_users_by_subscription_status(days: int = None, expired: bool = False):
    """Возвращает пользователей, у которых истекает или истекла подписка."""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor(aiomysql.DictCursor) as cur:
            if expired:
                # Получаем пользователей с просроченной подпиской
                await cur.execute("""
                    SELECT user_id, subscription_end FROM users
                    WHERE subscription_end IS NOT NULL AND subscription_end < NOW()
                """)
            else:
                # Получаем пользователей, у которых подписка истекает через `days` дней
                await cur.execute("""
                    SELECT user_id, subscription_end FROM users
                    WHERE subscription_end IS NOT NULL 
                    AND subscription_end BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL %s DAY)
                """, (days,))
            users = await cur.fetchall()
    return users

async def get_all_users():
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor(aiomysql.DictCursor) as cur:
            # Получаем всех пользователей, считая оставшиеся дни подписки
            await cur.execute("""
                SELECT 
                    user_id, 
                    username, 
                    DATEDIFF(subscription_end, NOW()) AS remaining_days
                FROM users
                WHERE status IS NULL AND subscription_end IS NOT NULL AND DATEDIFF(subscription_end, NOW()) >= 0
                ORDER BY remaining_days ASC
            """)
            users = await cur.fetchall()

    return users

async def get_remaining_questions(user_id: int):
    """Получает количество оставшихся вопросов у пользователя"""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor() as cur:
            await cur.execute("SELECT questions_count FROM users WHERE user_id = %s", (user_id,))
            result = await cur.fetchone()
            return result[0] if result else 0

async def decrement_questions(user_id: int):
    """Уменьшает количество доступных вопросов у пользователя на 1"""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor() as cur:
            await cur.execute("UPDATE users SET questions_count = questions_count - 1 WHERE user_id = %s", (user_id,))

async def reset_questions():
    """Обновляет количество вопросов у всех пользователей до 2"""
    await ensure_db_pool()
    async with db_pool.acquire() as conn:
        async with conn.cursor() as cur:
            await cur.execute("UPDATE users SET questions_count = 2")

