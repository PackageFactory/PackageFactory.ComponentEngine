test::
	./vendor/bin/phpunit \
		--enforce-time-limit \
		--bootstrap vendor/autoload.php \
		--testdox test/Integration \
		--coverage-html build/coverage-report \
		--coverage-filter src

test-unit::
	./vendor/bin/phpunit \
		--enforce-time-limit \
		--bootstrap vendor/autoload.php \
		--testdox test/Unit \
		--coverage-html build/coverage-report \
		--coverage-filter src

test-filter::
	./vendor/bin/phpunit \
		--bootstrap vendor/autoload.php \
		--testdox \
		--filter $(filter)
