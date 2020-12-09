.DEFAULT_GOAL:= help

.PHONY: tests
tests: phpstan unit-tests integration-tests

.PHONY: phpstan
phpstan: ## Performs static code analysis
	@vendor/bin/phpstan analyse

.PHONY: unit-tests
unit-tests: ## Executes the unit test suite
	@vendor/bin/phpunit -v --testsuite unit

.PHONY: integration-tests
integration-tests: ## Executes the integration test suite
	@vendor/bin/phpunit -v --testsuite integration

.PHONY: coverage
coverage: ## Executes the test suite and generates code coverage reports
	@vendor/bin/phpunit -v --coverage-html=build/coverage

.PHONY: view-coverage
view-coverage: ## Shows the code coverage report
	open build/coverage/index.html

.PHONY: docs
docs: ## Builds the documentation
	$(MAKE) -C docs html

.PHONY: view-docs
view-docs: ## Shows the documentation
	open docs/_build/html/index.html

.PHONY: help
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-16s\033[0m %s\n", $$1, $$2}'
