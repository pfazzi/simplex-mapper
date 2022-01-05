cs-fix:
	tools/php-cs-fixer/vendor/bin/php-cs-fixer fix --config .php-cs-fixer.dist.php -v

psalm:
	./vendor/bin/psalm

test:
	./vendor/bin/phpunit

build: cs-fix psalm test