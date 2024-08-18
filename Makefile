.PHONY: setup snapshot tests
PHP = php

tests:
	$(PHP) vendor/bin/phpunit tests/ -c phpunit.xml

test-all:
	$(PHP) vendor/bin/phpmd src/ text phpmd.xml
	$(PHP) vendor/bin/phpmd tests/ text phpmd.xml
	$(PHP) vendor/bin/psalm -c psalm.xml
	$(PHP) vendor/bin/phpunit tests/ -c phpunit.xml

infection:
	$(PHP) vendor/bin/infection --threads=2
