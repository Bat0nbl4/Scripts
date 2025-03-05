import random
import string
import re

def generate_short_key(length=12):
    """Генерирует случайный короткий идентификатор (из букв и цифр)"""
    return ''.join(random.choices(string.ascii_uppercase + string.digits, k=length))

def escape_md_v2(text: str) -> str:
    """Экранирует специальные символы для MarkdownV2"""
    escape_chars = r"\_*[]()~`>#+-=|{}.!<>"
    return "".join(f"\\{char}" if char in escape_chars else char for char in text)

def is_hash(text:str) -> bool:
    return bool(re.fullmatch(r"[0-9a-fA-F]{64}|0x[0-9a-fA-F]{64}|[0-9]{19}", text))