---
title: Error Formatters
---

PHPStan outputs errors via so-called error formatters. See the [list of built-in error formatters](/user-guide/output-format).

You can implement your own format by implementing the [`PHPStan\Command\ErrorFormatter\ErrorFormatter`](https://github.com/phpstan/phpstan-src/blob/master/src/Command/ErrorFormatter/ErrorFormatter.php) interface in a new class and add it to the configuration.

This is how the `ErrorFormatter` interface looks like:

```php
namespace PHPStan\Command\ErrorFormatter;

interface ErrorFormatter
{

	/**
	 * Formats the errors and outputs them to the console.
	 *
	 * @param \PHPStan\Command\AnalysisResult $analysisResult
	 * @param \PHPStan\Command\Output $style
	 * @return int Error code.
	 */
	public function formatErrors(
		\PHPStan\Command\AnalysisResult $analysisResult,
		\PHPStan\Command\Output $output
	): int;

}
```

Before you can start using your custom error formatter, you have to register it in the [configuration file](/config-reference):

```yaml
services:
	errorFormatter.awesome:
		class: App\PHPStan\AwesomeErrorFormatter
```

Use the name part after `errorFormatter.` as the CLI option value:

```bash
vendor/bin/phpstan analyse -c phpstan.neon \
	-l 4 \
	--error-format awesome \
	src tests
```
