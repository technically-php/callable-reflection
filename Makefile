.PHONY: tests

tests: vendor
	composer run test

vendor: composer.json composer.lock

composer.lock: composer.json
	composer install
	touch vendor
