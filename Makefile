.PHONY: test

test: vendor
	composer run test

vendor: composer.json composer.lock
	composer install
	touch vendor
