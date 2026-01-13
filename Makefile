include .env

production:
	docker compose --profile $(TARGET) up --build -d
	docker exec umechan-db /bin/sh -c "mysql -u${MYSQL_USERNAME} -p${MYSQL_PASSWORD} -e 'CREATE DATABASE IF NOT EXISTS ${MYSQL_CHAN_DATABASE}'"
ifeq ($(TARGET),production)
	docker exec umechan-db /bin/sh -c "mysql -u${MYSQL_USERNAME} -p${MYSQL_PASSWORD} -e 'CREATE DATABASE IF NOT EXISTS ${MYSQL_RADIO_DATABASE}'"
	docker restart umechan-migrations
endif
down:
	docker compose --profile $(TARGET) down

development:
	docker compose -f docker-compose.yml -f docker-compose.dev.yml --profile $(TARGET) up --build -d
	docker exec umechan-api composer install
	docker exec umechan-filestore composer install
	$(MAKE) generate-frontend-env
	docker exec umechan-db /bin/sh -c "mysql -u${MYSQL_USERNAME} -p${MYSQL_PASSWORD} -e 'DROP DATABASE IF EXISTS ${MYSQL_CHAN_DATABASE}'"
	docker exec umechan-db /bin/sh -c "mysql -u${MYSQL_USERNAME} -p${MYSQL_PASSWORD} -e 'CREATE DATABASE ${MYSQL_CHAN_DATABASE}'"
	docker restart umechan-migrations
	$(MAKE) chan-create-board tag=b name=Bred

generate-frontend-env:
	echo "VUE_APP_API_URL=${API_URL}" > frontend/.env.dev
	echo "VUE_APP_FILESTORE_URL=${FILESTORE_URL}" >> frontend/.env.dev
	echo "VUE_APP_ICECAST_URL=${ICECAST_URL}" >> frontend/.env.dev
	echo "VUE_APP_BASE_URL=${BASE_URL}" >> frontend/.env.dev
	echo "VUE_APP_DEFAULT_POSTER=${DEFAULT_NAME}" >> frontend/.env.dev
	echo "VUE_APP_MAX_FILESIZE=${MAX_FILESIZE}" >> frontend/.env.dev

restore-chan-database-from-dump:
	docker exec umechan-db /bin/sh -c "mysql -u${MYSQL_USERNAME} -p${MYSQL_PASSWORD} ${MYSQL_CHAN_DATABASE} < /tmp/dumps/$(DTBS_DUMP_PATH)"

backup-chan-database-to-dump:
	docker exec umechan-db /bin/sh -c "mysqldump -u${MYSQL_USERNAME} -p${MYSQL_PASSWORD} ${MYSQL_CHAN_DATABASE} > /tmp/dumps/$(DTBS_DUMP_PATH)"

backup-radio-database-to-dump:
	docker exec umechan-db /bin/sh -c "mysqldump -u${MYSQL_USERNAME} -p${MYSQL_PASSWORD} ${MYSQL_RADIO_DATABASE} > /tmp/dumps/umeradio.sql"

restore-chan-database-from-epds-dump:
	docker exec umechan-api php index.php posts:restore-from-epds-dump $(timestamp)

chan-set-sticky-thread:
	docker exec umechan-api php index.php posts:set-sticky-thread $(thread_id)
chan-unset-sticky-thread:
	docker exec umechan-api php index.php posts:unset-sticky-thread $(thread_id)

chan-set-blocked-thread:
	docker exec umechan-api php index.php posts:set-blocked-thread $(thread_id)
chan-unset-blocked-thread:
	docker exec umechan-api php index.php posts:unset-blocked-thread $(thread_id)

chan-create-board:
	docker exec umechan-api php index.php boards:create $(tag) $(name)
