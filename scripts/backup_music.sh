#!/usr/bin/env bash

source .env

if [ -z "${MUSIC_DIR_PATH:-}" ]; then
    echo "Ошибка: Переменная окружения MUSIC_DIR_PATH не установлена."
    exit 1
fi

if [ -z "${MUSIC_BACKUP_DIR:-}" ]; then
    echo "Ошибка: Переменная окружения MUSIC_BACKUP_DIR не установлена."
    exit 1
fi

if [ ! -d "$MUSIC_DIR_PATH" ]; then
    echo "Ошибка: Директория $MUSIC_DIR_PATH не существует."
    exit 1
fi

mkdir -p $MUSIC_BACKUP_DIR

files1=$(find "$MUSIC_DIR_PATH" -maxdepth 1 -type f -printf '%f\n' | sort)
files2=$(find "$MUSIC_BACKUP_DIR" -maxdepth 1 -type f -printf '%f\n' | sort)

declare -A files1_dict
declare -A files2_dict

for file in $files1; do
    files1_dict["$file"]=1
done

for file in $files2; do
    files2_dict["$file"]=1
done

for file in $files1; do
    if [ -z "${files2_dict[$file]}" ]; then
        echo "Копирование $file из $MUSIC_DIR_PATH в $MUSIC_BACKUP_DIR"
        cp "$MUSIC_DIR_PATH/$file" "$MUSIC_BACKUP_DIR/"
    fi
done

for file in $files2; do
    if [ -z "${files1_dict[$file]}" ]; then
        echo "Удаление $file из $MUSIC_BACKUP_DIR"
        rm "$MUSIC_BACKUP_DIR/$file"
    fi
done

echo "Синхронизация завершена."
