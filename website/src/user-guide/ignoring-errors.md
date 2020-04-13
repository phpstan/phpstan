---
title: Ignoring Errors
---

You might want to ignore some errors found by PHPStan for various reasons:

* The error can be a real issue that needs some refactoring of your codebase which you currently don't have time for.
* A PHPStan extension must be written to make PHPStan understand what the affected code really does and you choose not to do it right now.
* It's a genuine PHPStan bug and you don't want to (and shouldn't) wait for a bugfix.

<div class="bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-4 mb-4" role="alert">

Please note that some selected serious errors (like autoloading issues, parent class not found, parse errors, etc.) cannot be ignored and must be solved to get to zero errors when running PHPStan.

</div>

Errors can be ignored by adding a regular expression to the config file under the `ignoreErrors` key. To ignore an error by a regular expression in the whole project, add a string entry:

```yaml
parameters:
	ignoreErrors:
		- '#Call to an undefined method [a-zA-Z0-9\\_]+::doFoo\(\)#'
		- '#Call to an undefined method [a-zA-Z0-9\\_]+::doBar\(\)#'
```

To ignore errors by a regular expression only in a specific file, add an entry with `message` and `path` or `paths` keys. Wildcard patterns compatible with the PHP [`fnmatch()`](https://www.php.net/manual/en/function.fnmatch.php) are also supported. You can specify how many times the error is expected by using `count` (optional, applies only to `path`, not `paths`).

```yaml
parameters:
	ignoreErrors:
		-
			message: '#Call to an undefined method [a-zA-Z0-9\\_]+::doFoo\(\)#'
			path: some/dir/SomeFile.php
			count: 2
		-
			message: '#Call to an undefined method [a-zA-Z0-9\\_]+::doBar\(\)#'
			paths:
				- some/dir/*
				- other/dir/*
		- '#Other error to ignore everywhere#'
```

Relative paths in the `path` and `paths` keys are resolved based on the directory of the config file is in. So if your `phpstan.neon` is in the root directory of the project, and you want to ignore an error in `src/Foo/Bar.php`, your path key can simply be `src/Foo/Bar.php`.

<div class="bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-4 mb-4" role="alert">

Don't forget that you're writing regular expressions, so special characters like `|`, `$`, `.`, `(` and `)` need to be escaped. When in doubt, use an online regex testing tool like [regex101.com](https://regex101.com/) to make sure you're ignoring what you intend to ignore.

</div>

The Baseline
------------------

If you want to ignore all the current errors and only focus on new and changed code from now on, [go and learn about the baseline](/user-guide/baseline) feature.

Reporting unused ignores
------------------

If some of the patterns do not occur in the result anymore, PHPStan will let you know and you will have to remove the pattern from the configuration. You can turn off this behaviour by setting `reportUnmatchedIgnoredErrors` to `false` in the configuration:

```yaml
parameters:
	reportUnmatchedIgnoredErrors: false
```

Excluding whole files
------------------

If your codebase contains some files that are broken on purpose (e. g. to test behaviour of your application on files with invalid PHP code), you can exclude them using the `excludes_analyse` key. Each entry is used as a pattern for the [`fnmatch()`](https://www.php.net/manual/en/function.fnmatch.php) function.

```yaml
parameters:
	excludes_analyse:
		- tests/*/data/*
```
