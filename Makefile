.PHONY: setup snapshot tests
PHP = php

test-all:
	$(PHP) vendor/bin/phpmd src/ text phpmd.xml
	$(PHP) vendor/bin/psalm -c psalm.xml --no-cache
	XDEBUG_MODE=coverage $(PHP) vendor/bin/phpunit -c phpunit.xml --coverage-clover clover.xml
	$(PHP) vendor/bin/infection --threads=4
	$(PHP) vendor/bin/phpbench run --config=phpbench.json
	$(PHP) vendor/bin/coverage-check clover.xml 100

tests:
	$(PHP) vendor/bin/phpunit -c phpunit.xml

print-coverage:
	XDEBUG_MODE=coverage $(PHP) vendor/bin/phpunit -c phpunit.xml --coverage-text

coverage:
	XDEBUG_MODE=coverage $(PHP) vendor/bin/phpunit -c phpunit.xml --coverage-clover clover.xml
	$(PHP) vendor/bin/coverage-check clover.xml 100

infection:
	$(PHP) vendor/bin/infection --threads=4

bench:
	$(PHP) vendor/bin/phpbench run --config=phpbench.json
