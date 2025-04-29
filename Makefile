include .env

production:
	docker compose --profile $(TARGET) up --build -d
	docker exec umechan-api vendor/bin/phinx migrate
ifeq ($(TARGET),production)
	docker exec umechan-radio-metrics ./vendor/bin/phinx migrate
endif
down:
	docker compose --profile $(TARGET) down

development:
	docker compose -f docker-compose.yml -f docker-compose.dev.yml --profile $(TARGET) up --build -d
	docker exec umechan-api composer install
	docker exec umechan-filestore composer install
	$(MAKE) generate-env
	$(MAKE) restore-from-dump
	docker exec umechan-api ./vendor/bin/phinx migrate

restore-from-dump:
ifeq ($(shell test -e ./data/dumps/dump.sql && echo -n yes),yes)
	docker exec umechan-db /bin/sh -c "mysql -u${MYSQL_USERNAME} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE} < /tmp/dumps/dump.sql"
endif

generate-env:
	echo "VUE_APP_API_URL=${API_URL}" > frontend/.env.dev
	echo "VUE_APP_FILESTORE_URL=${FILESTORE_URL}" >> frontend/.env.dev
	echo "VUE_APP_ICECAST_URL=${ICECAST_URL}" >> frontend/.env.dev
	echo "VUE_APP_BASE_URL=${BASE_URL}" >> frontend/.env.dev
	echo "VUE_APP_DEFAULT_POSTER=${DEFAULT_NAME}" >> frontend/.env.dev
	echo "VUE_APP_MAX_FILESIZE=${MAX_FILESIZE}" >> frontend/.env.dev
