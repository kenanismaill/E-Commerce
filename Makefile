
.PHONY: run
run:
	@if [ ! -e ".env.local" ]; then\
		cp .env .env.local; \
	fi
	@docker-compose up -d
	@echo "Service is running on http://localhost:9002"

.PHONY: install
install:
	@docker-compose exec --user="php" -T e-commerce composer install

.PHONY: migrate
migrate:
	@docker-compose exec --user="php" e-commerce php bin/console doctrine:migrations:migrate

.PHONY: stop
stop:
	@docker-compose stop

.PHONY: enter
enter:
	@docker-compose exec --user="php" e-commerce /bin/sh

.PHONY: enter-as-root
enter-as-root:
	@docker-compose exec --user="root" e-commerce /bin/sh

.PHONY: test
test:
	@docker-compose exec --user="php" -T e-commerce /bin/sh -c 'APP_ENV="test" ./bin/phpunit --testdox'

.PHONY: destroy
destroy:
	@docker-compose down --rmi local --remove-orphans

