analyse::
	./vendor/bin/phpstan analyse --level 8 src test 

test::
	./vendor/bin/phpunit \
		--enforce-time-limit \
		--bootstrap vendor/autoload.php \
		--testdox test \
		--coverage-html build/coverage-report \
		--whitelist src

test-filter::
	./vendor/bin/phpunit \
		--bootstrap vendor/autoload.php \
		--testdox test \
		--filter $(filter)

update::
	./vendor/bin/phpunit \
		--bootstrap vendor/autoload.php \
		--testdox test \
		-d --update-snapshots