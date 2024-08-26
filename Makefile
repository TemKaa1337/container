.PHONY: setup snapshot tests
PHP = php

tests:
	$(PHP) vendor/bin/phpunit -c phpunit.xml

coverage:
	XDEBUG_MODE=coverage $(PHP) vendor/bin/phpunit -c phpunit.xml --coverage-text

test-all:
	$(PHP) vendor/bin/phpmd src/ text phpmd.xml
	$(PHP) vendor/bin/psalm -c psalm.xml --no-cache
	$(PHP) vendor/bin/phpunit -c phpunit.xml
	$(PHP) vendor/bin/infection --threads=4

infection:
	$(PHP) vendor/bin/infection --threads=4
