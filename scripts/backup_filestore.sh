#!/bin/bash

set -euo pipefail
source .env

if [ -z "${FILESTORE_SOURCE_DIR:-}" ]; then
    echo "Ошибка: Переменная окружения FILESTORE_SOURCE_DIR не установлена."
    exit 1
fi

if [ -z "${FILESTORE_BACKUP_DIR:-}" ]; then
    echo "Ошибка: Переменная окружения FILESTORE_BACKUP_DIR не установлена."
    exit 1
fi

if [ -z "${FILESTORE_BACKUP_INDEX_DB:-}" ]; then
    echo "Ошибка: Переменная окружения FILESTORE_BACKUP_INDEX_DB не установлена."
    exit 1
fi

if [ -z "${BACKUP_PASSPHRASE:-}" ]; then
    echo "Ошибка: Переменная окружения BACKUP_PASSPHRASE не установлена."
    exit 1
fi

if [ ! -d "$FILESTORE_SOURCE_DIR" ]; then
    echo "Ошибка: Источник $FILESTORE_SOURCE_DIR не существует или не является директорией."
    exit 1
fi

mkdir -p "$FILESTORE_BACKUP_DIR"

sqlite3 "$FILESTORE_BACKUP_INDEX_DB" <<EOF
CREATE TABLE IF NOT EXISTS backup_index (
    filename TEXT NOT NULL,
    archive_name TEXT NOT NULL
);
EOF

get_files_with_dates() {
    find "$FILESTORE_SOURCE_DIR" -type f -printf '%TY-%Tm-%Td\t%p\n'
}

echo "Запуск бекапа..."
echo "Источник: $FILESTORE_SOURCE_DIR"
echo "Цель: $FILESTORE_BACKUP_DIR"

FILES_DATA=$(get_files_with_dates | sort)

if [ -z "$FILES_DATA" ]; then
    echo "Нет файлов для бекапа."
    exit 0
fi

DATES=$(echo "$FILES_DATA" | cut -f1 | sort -u)

for DATE in $DATES; do
    echo "Обработка даты: $DATE"
    
    ARCHIVE_NAME="backup_${DATE}.tar.gz.gpg"
    ARCHIVE_PATH="${FILESTORE_BACKUP_DIR}/${ARCHIVE_NAME}"
    
    if [ -f "$ARCHIVE_PATH" ]; then
        EXISTING_COUNT=$(sqlite3 "$FILESTORE_BACKUP_INDEX_DB" "SELECT COUNT(*) FROM backup_index WHERE archive_name='$ARCHIVE_NAME';")
        if [ "$EXISTING_COUNT" -gt 0 ]; then
            echo "  Архив $ARCHIVE_NAME уже существует и индексирован. Пропускаем."
        else
            echo "  Архив $ARCHIVE_NAME существует, но не индексирован. Пропускаем."
        fi
        continue
    fi

    FILES_FOR_DATE=$(echo "$FILES_DATA" | awk -F'\t' -v date="$DATE" '$1 == date {print $2}')
    
    if [ -z "$FILES_FOR_DATE" ]; then
        echo "  Нет файлов для даты $DATE."
        continue
    fi

    TEMP_FILELIST=$(mktemp)
    echo "$FILES_FOR_DATE" > "$TEMP_FILELIST"
    
    echo "  Создание архива $ARCHIVE_NAME..."
    tar -czf - -T "$TEMP_FILELIST" | \
        gpg --batch --yes --passphrase "${BACKUP_PASSPHRASE}" --symmetric --cipher-algo AES256 -o "$ARCHIVE_PATH"
    
    rm -f "$TEMP_FILELIST"
    
    echo "  Индексация файлов в архиве $ARCHIVE_NAME..."
    
    TEMP_SQL=$(mktemp)
    
    echo "$FILES_FOR_DATE" | while read -r filename; do
        safe_filename=$(echo "$filename" | sed "s/'/''/g")
        safe_archive=$(echo "$ARCHIVE_NAME" | sed "s/'/''/g")
        echo "INSERT OR IGNORE INTO backup_index (filename, archive_name) VALUES ('$safe_filename', '$safe_archive');" >> "$TEMP_SQL"
    done
    
    if [ -s "$TEMP_SQL" ]; then
        sqlite3 "$FILESTORE_BACKUP_INDEX_DB" <<EOF
BEGIN TRANSACTION;
$(cat "$TEMP_SQL")
COMMIT;
EOF
    fi
    
    rm -f "$TEMP_SQL"
    
    echo "  Готово."
done

echo "Бекап завершен."
