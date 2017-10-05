NAME = mrpassword
VERSION = 4.0.1

.PHONY: create-network build-dev install-dev uninstall-dev

create-network:
	$(if $(shell docker network ls | grep mrpassword),,docker network create mrpassword)

build-dev: create-network
	docker-compose -f docker-compose.dev.yml build

install-dev: create-network
	docker-compose -f docker-compose.dev.yml up -d

uninstall-dev:
	docker-compose -f docker-compose.dev.yml down
