.PHONY: setup snapshot tests
PHP = php

test-all:
	$(PHP) vendor/bin/phpmd src/ text phpmd.xml
	$(PHP) vendor/bin/psalm -c psalm.xml --no-cache
	$(PHP) vendor/bin/phpunit -c phpunit.xml
	$(PHP) vendor/bin/infection --threads=4
	$(PHP) vendor/bin/phpbench run --config=phpbench.json

tests:
	$(PHP) vendor/bin/phpunit -c phpunit.xml

coverage:
	XDEBUG_MODE=coverage $(PHP) vendor/bin/phpunit -c phpunit.xml --coverage-text

infection:
	$(PHP) vendor/bin/infection --threads=4

bench:
	$(PHP) vendor/bin/phpbench run --config=phpbench.json
