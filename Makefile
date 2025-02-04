# Variables
PHP = php
SYMFONY_CONSOLE = $(APP_FOLDER) $(PHP) bin/console

## —— 📚 Database ——
create-database: ## Création de la base de donnée
		$(SYMFONY_CONSOLE) doctrine:database:create

save-database: ## Enregistrer dans la base de donnée
		$(MAKE) make-migration
		$(MAKE) migration-migrate
		$(MAKE) fixtures

## —— 🎶 Symfony ——
make-migration: ## Création de la migration 
		$(SYMFONY_CONSOLE) make:migration

migration-migrate: ## Création de la migration 
		$(SYMFONY_CONSOLE) doctrine:migrations:migrate --no-interaction

fixtures: ## Création des fixtures
		$(SYMFONY_CONSOLE) doctrine:fixtures:load

## —— 🛠️ Others ——
help: ## List of commands
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'