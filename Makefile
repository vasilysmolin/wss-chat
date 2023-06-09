setup:
	composer install
	docker-compose up -d
	cp -n .env.example .env|| true
	docker-compose exec app-fpm php artisan key:gen --ansi
	docker-compose exec app-fpm php artisan migrate --force
	docker-compose exec app-fpm php artisan db:seed --force

start-back:
	php artisan serve --host 0.0.0.0 --port 80

start-socket:
	php artisan websocket-start

test:
	docker-compose exec app-fpm php artisan test

migrate:
	docker-compose exec app-fpm php artisan migrate

autoload:
	docker-compose exec app-fpm composer install

test-coverage:
	composer exec --verbose phpunit tests -- --coverage-clover build/logs/clover.xml

lint:
	composer exec phpcs -v

lint-fix:
	composer exec phpcbf -v

phpstan:
	composer exec phpstan analyse

env-prepare:
	cp -n .env.example .env || true

key:
	php artisan key:generate

ide-helper:
	php artisan ide-helper:eloquent
	php artisan ide-helper:gen
	php artisan ide-helper:generate
	php artisan ide-helper:model
	php artisan ide-helper:meta
	php artisan ide-helper:mod -n

update:
	git pull
	docker-compose exec app-fpm composer install --no-interaction --ansi --no-suggest
	docker-compose exec app-fpm php artisan migrate --force
	docker-compose exec app-fpm php artisan optimize

heroku-build:
	php artisan migrate --force
	php artisan db:seed --force
	php artisan optimize

start-front:
	npm run dev

setup-ci: env-prepare-ci install-ci key-ci database-prepare-ci seed-ci

env-prepare-ci:
	cp -n .env.example .env || true

key-ci:
	php artisan key:gen --ansi
	php artisan jwt:secret --force

test-coverage-ci:
	composer exec --verbose phpunit tests -- --coverage-clover build/logs/clover.xml

install-ci:
	composer install

seed-ci:
	php artisan db:seed --force
	docker-compose exec app-fpm php artisan db:seed --class="Database\Seeders\TimeZonesTableSeeder" --force

optimize-ci:
	php artisan optimize

database-prepare-ci:
	php artisan migrate
