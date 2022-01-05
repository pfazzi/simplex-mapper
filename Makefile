compose-update:
	composer update

cs-fix:
	tools/php-cs-fixer/vendor/bin/php-cs-fixer fix --config .php-cs-fixer.dist.php -v

psalm:
	./vendor/bin/psalm

test:
	./vendor/bin/phpunit

build: compose-update cs-fix psalm test