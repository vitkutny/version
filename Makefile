composer:
	composer validate
	composer install --no-interaction --prefer-dist

phpstan:
	vendor/bin/phpstan analyse src/ --level 8 --no-progress

cs:
	vendor/bin/phpcs src/ --standard=vendor/pd/coding-standard/src/PeckaCodingStandard/ruleset.xml
	vendor/bin/phpcs src/ --standard=vendor/pd/coding-standard/src/PeckaCodingStandardStrict/ruleset.xml

run-tests:
	vendor/bin/tester -C tests/
