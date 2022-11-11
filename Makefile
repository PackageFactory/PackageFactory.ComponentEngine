analyse::
	./vendor/bin/phpstan analyse -c phpstan.neon

test::
	./vendor/bin/phpunit \
		--enforce-time-limit \
		--bootstrap vendor/autoload.php \
		--testdox test/Integration \
		--coverage-html build/coverage-report \
		--whitelist src

test-unit::
	./vendor/bin/phpunit \
		--enforce-time-limit \
		--bootstrap vendor/autoload.php \
		--testdox test/Unit \
		--coverage-html build/coverage-report \
		--whitelist src

test-filter::
	./vendor/bin/phpunit \
		--bootstrap vendor/autoload.php \
		--testdox \
		--filter $(filter)

update::
	./vendor/bin/phpunit \
		--bootstrap vendor/autoload.php \
		--testdox test \
		-d --update-snapshots