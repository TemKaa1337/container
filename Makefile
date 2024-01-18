.PHONY: setup snapshot tests
PHP = php

tests:
	$(PHP) vendor/bin/phpmd src/ text phpmd.xml
	$(PHP) vendor/bin/psalm -c psalm.xml
	$(PHP) vendor/bin/phpunit tests/
