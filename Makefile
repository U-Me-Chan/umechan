up:
	docker-compose -f docker-compose.prod.yml up --build -d
	docker exec pissykaka-api ./vendor/bin/phinx migrate
down:
	docker-compose -f docker-compose.prod.yml down
up-dev:
	docker-compose -f docker-compose.dev.yml up --build -d
	cd frontend && npm run serve -- --port 80 --mode dev
down-dev:
	docker-compose -f docker-compose.dev.yml down
