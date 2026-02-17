---
title: "phpstan.classNotFound"
ignorable: false
---

## Code example

This error is reported during PHPStan's internal testing when a class being analysed cannot be found via the reflection provider.

## Why is it reported?

The class under analysis is not autoloadable. This typically occurs when the `autoload-dev` section in `composer.json` is not configured to include the test directories, or when the class file is not included in the analysis paths.

## How to fix it

Ensure your `composer.json` `autoload-dev` section includes the directory containing the class:

```json
{
	"autoload-dev": {
		"psr-4": {
			"App\\Tests\\": "tests/"
		}
	}
}
```

Then run `composer dump-autoload` to regenerate the autoloader.
