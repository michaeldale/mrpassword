NAME = mrpassword
VERSION = 4.0.1

.PHONY: create-network build-dev install-dev uninstall-dev rebuild-dev clean clean-config

create-network:
	$(if $(shell docker network ls | grep mrpassword),,docker network create mrpassword)

build-dev: create-network
	docker-compose -f docker-compose.dev.yml build

install-dev: create-network
	docker-compose -f docker-compose.dev.yml up -d

uninstall-dev:
	docker-compose -f docker-compose.dev.yml down

clean-config:
	-rm .htaccess .mrp_test_file user/settings/.mrp_test_file user/settings/config.php

clean: clean-config
	-docker rmi -f `docker images mrpassword/* -q`
	-docker volume rm `docker volume ls --filter name=mrpassword_* -q`

rebuild-dev: uninstall-dev build-dev clean-config install-dev
