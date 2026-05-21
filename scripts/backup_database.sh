#!/bin/bash

source .env

if [ -z "${MYSQL_USERNAME:-}" ]; then
    echo "Ошибка: Переменная окружения MYSQL_USERNAME не установлена."
    exit 1
fi

if [ -z "${MYSQL_PASSWORD:-}" ]; then
    echo "Ошибка: Переменная окружения MYSQL_PASSWORD не установлена."
    exit 1
fi

if [ -z "${MYSQL_CHAN_DATABASE:-}" ]; then
    echo "Ошибка: Переменная окружения MYSQL_CHAN_DATABASE не установлена."
    exit 1
fi

if [ -z "${MYSQL_RADIO_DATABASE:-}" ]; then
    echo "Ошибка: Переменная окружения MYSQL_RADIO_DATABASE не установлена."
    exit 1
fi

if [ -z "${BACKUP_PASSPHRASE:-}" ]; then
    echo "Ошибка: Переменная окружения BACKUP_PASSPHRASE не установлена."
    exit 1
fi  

if [ -z "${DATABASE_BACKUP_DIR:-}" ]; then
    echo "Ошибка: Переменная окружения BACKUP_DATABASE_DIR не установлена."
    exit 1
fi

CHAN_DATABASE_DUMP_FILE=$(date '+%Y.%m.%d-%H:%M:%S')-pissykaka.sql
RADIO_DATABASE_DUMP_FILE=$(date '+%Y.%m.%d-%H:%M:%S')-umeradio.sql

docker exec umechan-db mysqldump \
       -u$MYSQL_USERNAME \
       -p$MYSQL_PASSWORD \
       $MYSQL_CHAN_DATABASE \
    | gpg --symmetric \
          --cipher-algo AES256 \
          --batch --yes \
          --passphrase $BACKUP_PASSPHRASE \
          -o $DATABASE_BACKUP_DIR/"${CHAN_DATABASE_DUMP_FILE}.gpg"

docker exec umechan-db mysqldump \
       -u$MYSQL_USERNAME \
       -p$MYSQL_PASSWORD \
       $MYSQL_RADIO_DATABASE \
    | gpg --symmetric \
          --cipher-algo AES256 \
          --batch --yes \
          --passphrase $BACKUP_PASSPHRASE \
          -o $DATABASE_BACKUP_DIR/"${RADIO_DATABASE_DUMP_FILE}.gpg"
