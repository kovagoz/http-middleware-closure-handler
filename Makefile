composer_cmd := docker run --rm -it -v $(PWD):/host -w /host composer:2

.PHONY: test
test: | vendor
	docker run --rm -it -v $(PWD):/host -w /host php:8-alpine ./vendor/bin/phpunit test

.PHONY: shell
shell:
	docker run --rm -it -v $(PWD):/host -w /host composer:2 /bin/sh

vendor:
	$(composer_cmd) install

.PHONY: autoloader
autoloader:
	$(composer_cmd) dump-autoload

.PHONY: composer-validate
composer-validate:
	$(composer_cmd) validate --strict
