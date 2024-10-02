---
title: Error Formatters
---

PHPStan outputs errors via so-called error formatters. See the [list of built-in error formatters](/user-guide/output-format).

You can implement your own format by implementing the [`PHPStan\Command\ErrorFormatter\ErrorFormatter`](https://apiref.phpstan.org/2.0.x/PHPStan.Command.ErrorFormatter.ErrorFormatter.html) interface in a new class and add it to the configuration.

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

Special format for Continuous Integration (CI)
---------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.7.2</div>

PHPStan ships with `PHPStan\Command\ErrorFormatter\CiDetectedErrorFormatter` that can detect when PHPStan runs in a CI environment.

If your error formatter is targeted for human consumption and is not supposed to be machine-readable, you can ask for `CiDetectedErrorFormatter` in your constructor and call it alongside your own format output.

```php
use PHPStan\Command\ErrorFormatter\CiDetectedErrorFormatter;

final class MyCustomErrorFormat implements ErrorFormatter
{
    public function __construct(
        private CiDetectedErrorFormatter $ciDetectedErrorFormatter,
    ) {
    }

    public function formatErrors(AnalysisResult $analysisResult, Output $output) : int
    {
        // Output special CI format when supported CI is detected
        $this->ciDetectedErrorFormatter->formatErrors($analysisResult, $output);

        // Custom format here...

        return 0;
    }
}
```

When PHPStan runs in CI, it's going to output errors according to the detected environment like GitHub Actions and TeamCity.

When PHPStan does not run in CI, it's not going to output anything.
