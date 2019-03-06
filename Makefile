.PHONY: tests

quality: tests phpstan cs

tests:
	vendor/bin/phpunit

phpstan:
	vendor/bin/phpstan analyse --level max src

cs:
	vendor/bin/php-cs-fixer fix --dry-run --diff

cs-fix:
	vendor/bin/php-cs-fixer fix
