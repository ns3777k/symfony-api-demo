#
# BEGIN IN-DOCKER
#

.PHONY: lint
lint:
	composer validate --strict
	bin/console lint:container
	bin/console lint:yaml config/services.yaml --parse-tags
	vendor/bin/php-cs-fixer fix -v --dry-run --stop-on-violation
	vendor/bin/phpstan analyse --memory-limit=1G

#
# END IN-DOCKER
#

#
# BEGIN DEV
#

UNAME=$(shell uname)
USERID=$(shell id -u)
GROUPID=$(shell id -g)
DOCKERUSER="$(USERID):$(GROUPID)"
DOCKER_RUN_OPTS=-u $(DOCKERUSER) --rm -it
COMPOSER_IMAGE=composer:2.0.3
PHP_DEV_IMAGE=php-custom:7.4-fpm-buster
DOCKERCOMPOSE=DOCKERUSER=$(DOCKERUSER) COMPOSER_IMAGE=$(COMPOSER_IMAGE) PHP_DEV_IMAGE=$(PHP_DEV_IMAGE) docker-compose
DOCKERCOMPOSE_UP_OPTS=--force-recreate --abort-on-container-exit --build

ifeq ($(UNAME), Linux)
	USERGROUP:=$(shell getent group $(GROUPID) | cut -d: -f1)
endif

ifeq ($(UNAME), Darwin)
	USERGROUP=staff
endif

.PHONY: dev-prepare-runtime
dev-prepare-runtime:
	@echo -e "Preparing runtime group and passwd files..."
	@echo "$(USER):x:$(USERID):$(GROUPID):$(USERGROUP):/home/$(USER):/bin/bash" > $(PWD)/docker/runtime/passwd
	@echo "$(USERGROUP):x:$(GROUPID):$(USER)" > $(PWD)/docker/runtime/group

.PHONY: dev-install
dev-install: dev-prepare-runtime
	@docker run $(DOCKER_RUN_OPTS) -v $(PWD):/app -v /tmp:/tmp $(COMPOSER_IMAGE) install

.PHONY: dev-composer-require
dev-composer-require:
	@docker run $(DOCKER_RUN_OPTS) -v $(PWD):/app -v /tmp:/tmp $(COMPOSER_IMAGE) require $(PACKAGE)

.PHONY: dev-composer-update
dev-composer-update:
	@docker run $(DOCKER_RUN_OPTS) -v $(PWD):/app -v /tmp:/tmp $(COMPOSER_IMAGE) update $(PACKAGE)

.PHONY: dev-composer-require-dev
dev-composer-require-dev:
	@docker run $(DOCKER_RUN_OPTS) -v $(PWD):/app -v /tmp:/tmp $(COMPOSER_IMAGE) require --dev $(PACKAGE)

.PHONY: dev-docker-build
dev-docker-build:
	@docker build -t php-custom:7.4-fpm-buster -f docker/images/Dockerfile.dev docker/images

.PHONY: dev-start
dev-start:
	@$(DOCKERCOMPOSE) -f docker-compose.dev.yml up $(DOCKERCOMPOSE_UP_OPTS)

.PHONY: dev-lint
dev-lint:
	@docker run $(DOCKER_RUN_OPTS) -v $(PWD):/project $(PHP_DEV_IMAGE) make lint

#
# END DEV
#
