default: help

help:
	@echo "Please use 'make <target>' where <target> is one of"
	@echo "  tests                  Executes the Unit tests"
	@echo "  coverage               Creates the Coverage reports"
	@echo "  view-coverage          Creates and opens the Coverage reports"

tests:
	./vendor/bin/phpunit

travis-tests:
	./vendor/bin/phpunit --coverage-clover build/coverage.clover

coverage:
	./vendor/bin/phpunit --coverage-html build/coverage

view-coverage: coverage
	open build/coverage/index.html

tests/.env.enc:
	travis encrypt-file tests/.env tests/.env.enc -f

.PHONY: tests travis-tests coverage view-coverage
