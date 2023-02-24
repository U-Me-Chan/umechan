up:
	docker-compose -f docker-compose.prod.yml up --build -d
	docker exec umechan-api ./vendor/bin/phinx migrate
	docker exec umechan-radio-metrics ./vendor/bin/phinx migrate
down:
	docker-compose -f docker-compose.prod.yml down
up-dev:
	docker-compose -f docker-compose.dev.yml up --build -d
	docker exec umechan-api composer install
	docker exec umechan-api ./vendor/bin/phinx migrate
	docker exec umechan-filestore composer install
	docker exec umechan-radio-metrics ./vendor/bin/phinx migrate
down-dev:
	docker-compose -f docker-compose.dev.yml down
