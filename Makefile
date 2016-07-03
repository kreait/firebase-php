default: help

help:
	@echo "Please use 'make <target>' where <target> is one of"
	@echo "  tests                  Executes the Unit tests"
	@echo "  coverage               Creates the Coverage reports"
	@echo "  view-coverage          Creates and opens the Coverage reports"
	@echo "  travis-enc             Encrypts credentials for Travis"

tests:
	./vendor/bin/phpunit

travis-tests:
	./vendor/bin/phpunit --coverage-clover build/coverage.clover

coverage:
	./vendor/bin/phpunit --coverage-html build/coverage

view-coverage: coverage
	open build/coverage/index.html

tests/secrets.tar.enc:
	tar -C tests -cvf tests/secrets.tar .env google-service-account.json
	-docker run --rm -v $(PWD):/project travis-cli encrypt-file -f -r kreait/firebase-php /project/tests/secrets.tar /project/tests/secrets.tar.enc
	rm tests/secrets.tar

.PHONY: tests travis-tests coverage view-coverage
