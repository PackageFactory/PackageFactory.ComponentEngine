# Nothing to see here yet

## Development

For common automated development tasks, the repository contains several scripts which can be run via composer, like:

```sh
composer {script-name}
```

or, alternatively, directly via:

```sh
./scripts/{script-name}
```

| script-name | description |
|-|-|
| `analyse` | Run static code analysis with [phpstan](https://phpstan.org/) |
| `test` | Run tests with [phpunit](https://phpunit.de/) |

### Running tests

There's a test suite for unit tests and another one for integration tests. By default both test suites are run entirely. In order to just run one of them, you need to add the `--testsuite` parameter to the script call, e.g.:

```sh
composer test -- --testsuite unit
composer test -- --testsuite integration
```

or alternatively:

```sh
./scripts/test --testsuite unit
./scripts/test --testsuite integration
```

## License

see [LICENSE](./LICENSE)
