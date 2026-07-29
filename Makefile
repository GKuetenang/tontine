.PHONY: helpers

helpers:
	php artisan ide-helper:generate
	php artisan ide-helper:meta
	php artisan ide-helper:models -F helpers/ModelHelper.php -M 

.PHONY: analyse
analyse:
	./vendor/bin/phpstan analyse --no-progress

.PHONY: pint
pint:
	./vendor/bin/pint