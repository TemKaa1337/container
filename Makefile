.PHONY: setup snapshot tests
PHP = php

tests:
	$(PHP) vendor/bin/phpunit tests/ -c phpunit.xml

test-all:
	$(PHP) vendor/bin/phpmd src/ text phpmd.xml
	$(PHP) vendor/bin/phpmd tests/ text phpmd.xml
	$(PHP) vendor/bin/psalm -c psalm.xml --no-cache
	$(PHP) vendor/bin/phpunit tests/ -c phpunit.xml
	$(PHP) vendor/bin/infection --threads=2

infection:
	$(PHP) vendor/bin/infection --threads=2
