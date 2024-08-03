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

Ignoring in code using PHPDocs
-------------------

Errors can be ignored next to the violating line of code using `@phpstan-ignore` PHPDoc tag. All the PHP comment styles (`//`, `/* */`, `/** */`) can be used.

```php
function () {
	/** @phpstan-ignore variable.undefined */
	echo $foo;

	echo $foo; /** @phpstan-ignore variable.undefined */

	/* @phpstan-ignore variable.undefined */
	echo $foo;

	echo $foo; /* @phpstan-ignore variable.undefined */

	// @phpstan-ignore variable.undefined
	echo $foo;

	echo $foo; // @phpstan-ignore variable.undefined
};
```

The `@phpstan-ignore` comment requires an [error identifier](/error-identifiers) of the error you want to ignore. If the comment is the only thing on its line besides whitespace, it will look for an error to ignore on the next line. Otherwise it will ignore an error on its own line.

```php
function () {
	// @phpstan-ignore argument.type
	$this->foo->doSomethingWithString(1);

	$this->foo->doSomethingWithString(2); // @phpstan-ignore argument.type
};
```

Multiple errors can be ignored with comma-separated identifiers. Multiple errors with the same identifier can also be ignored the same way:

```php
echo $foo, $bar; // @phpstan-ignore variable.undefined, variable.undefined
```

You can find out the error identifier for the error you're trying to ignore:

* By running PHPStan with `-v` flag. The default `table` error formatter will output the error identifier below the error message.
* By running [PHPStan Pro](https://phpstan.org/blog/introducing-phpstan-pro){.phpstan-pro-label}. It will output your errors along with their identifiers in a beautiful web UI.
* By reproducing your error in the [playground](/try). It will output the identifier next to the error message.
* Custom [error formatters](/user-guide/output-format) also have the error identifiers at their disposal in order to output them.

The reason why a certain error is ignored using `@phpstan-ignore` can be put into parentheses after the identifier:

```php
echo $foo; // @phpstan-ignore variable.undefined (Because we are lazy)
```

You can also choose to ignore all errors on a specific line using `@phpstan-ignore-line` and `@phpstan-ignore-next-line`.

```php
echo $foo; // @phpstan-ignore-line

// @phpstan-ignore-next-line
echo $foo;
```

If your codebase is currently full of `@phpstan-ignore-line` & `@phpstan-ignore-next-line` and you'd like to switch to identifier-specific `@phpstan-ignore`, you can use automatic migration wizard in [PHPStan Pro](https://phpstan.org/blog/introducing-phpstan-pro){.phpstan-pro-label} that will do it for you:

<video class="w-full aspect-[1680/1080] mb-8 border border-gray-200 rounded-lg overflow-hidden" autoplay muted loop playsinline poster="/tmp/images/ignore-line-wizard-poster.jpg">
  <source src="/tmp/images/ignore-line-wizard.mp4" type="video/mp4">
</video>

Try out [PHPStan Pro](https://phpstan.org/blog/introducing-phpstan-pro){.phpstan-pro-label} by running PHPStan with `--pro` or by going to [account.phpstan.com](https://account.phpstan.com/) and creating an account.

Ignoring in configuration file
-------------------

Errors can be ignored by adding a regular expression to the [configuration file](/config-reference) under the `ignoreErrors` key. To ignore an error by a regular expression in the whole project, add a string entry:

```yaml
parameters:
	ignoreErrors:
		- '#Call to an undefined method [a-zA-Z0-9\\_]+::doFoo\(\)#'
		- '#Call to an undefined method [a-zA-Z0-9\\_]+::doBar\(\)#'
```

To ignore errors by a regular expression only in a specific file, add an entry with `message` or `messages` and `path` or `paths` keys. Wildcard patterns compatible with the PHP [`fnmatch()`](https://www.php.net/manual/en/function.fnmatch.php) are also supported. You can specify how many times the error is expected by using `count` (optional, applies only to `message` not `messages` and `path`, not `paths`).

```yaml
parameters:
	ignoreErrors:
		-
			message: '#Access to an undefined property [a-zA-Z0-9\\_]+::\$foo#'
			path: some/dir/SomeFile.php
		-
			message: '#Call to an undefined method [a-zA-Z0-9\\_]+::doBar\(\)#'
			paths:
				- some/dir/*
				- other/dir/*
		-
			messages:
				- '#Call to an undefined method [a-zA-Z0-9\\_]+::doFooFoo\(\)#'
				- '#Call to an undefined method [a-zA-Z0-9\\_]+::doFooBar\(\)#'
			path: other/dir/AnotherFile.php
		-
			messages:
				- '#Call to an undefined method [a-zA-Z0-9\\_]+::doFooFoo\(\)#'
				- '#Call to an undefined method [a-zA-Z0-9\\_]+::doFooBar\(\)#'
			paths:
				- some/foo/dir/*
				- other/foo/dir/*
		-
			message: '#Call to an undefined method [a-zA-Z0-9\\_]+::doFoo\(\)#'
			path: other/dir/DifferentFile.php
			count: 2 # optional
		- '#Other error to ignore everywhere#'
```

Relative paths in the `path` and `paths` keys are resolved based on the directory of the config file is in. So if your `phpstan.neon` is in the root directory of the project, and you want to ignore an error in `src/Foo/Bar.php`, your path key can simply be `src/Foo/Bar.php`.

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.11.0</div>

If you want to ignore only a specific error, you can take advantage of [error identifiers](/error-identifiers) in the `identifier` key:

```yaml
parameters:
	ignoreErrors:
		-
			message: '#Access to an undefined property [a-zA-Z0-9\\_]+::\$foo#'
			identifier: property.notFound
			path: some/dir/SomeFile.php
```

The reported error has to match both the `message` pattern and the `identifier` in order to be ignored.

You can also use only the `identifier` key to ignore all errors of the same type:

```yaml
parameters:
	ignoreErrors:
		-
			identifier: property.notFound
```

Viewing ignored errors
------------------

Did you know [PHPStan Pro](https://phpstan.org/blog/introducing-phpstan-pro){.phpstan-pro-label} lets you browse ignored errors in a beautiful web UI? It shows the errors right on the line where they'd be reported if they weren't ignored:

<video class="w-full aspect-[1656/1080] mb-8 border border-gray-200 rounded-lg overflow-hidden" autoplay muted loop playsinline poster="/tmp/images/phpstan-pro-ignored-errors-poster.jpg">
  <source src="/tmp/images/phpstan-pro-ignored-errors.mp4" type="video/mp4">
</video>

Try out [PHPStan Pro](https://phpstan.org/blog/introducing-phpstan-pro){.phpstan-pro-label} by running PHPStan with `--pro` or by going to [account.phpstan.com](https://account.phpstan.com/) and creating an account.

Generate an ignoreErrors entry
------------------

Using the fields below, you can generate an entry that you can put in the `parameters.ignoreErrors` section of your [configuration file](/config-reference). It deals with the complexity of writing a matching regular expression from a plain string and encoding that regular expression into the neon format.

{% include 'ignoreErrorsWidget.njk' %}

The Baseline
------------------

If you want to ignore all the current errors and only focus on new and changed code from now on, [go and learn about the baseline](/user-guide/baseline) feature.

Reporting unused ignores
------------------

If some of the ignored errors (both from configuration and PHPDocs) do not occur in the result anymore, PHPStan will let you know and you will have to remove the pattern from the configuration. You can turn off this behaviour by setting `reportUnmatchedIgnoredErrors` to `false` in the configuration:

```yaml
parameters:
	reportUnmatchedIgnoredErrors: false
```

You can turn on/off reporting unused ignores explicitly for each entry in `ignoredErrors`. This overwrites global `reportUnmatchedIgnoredErrors` setting.

```yaml
parameters:
	reportUnmatchedIgnoredErrors: false
	ignoreErrors:
		- '#This message will not be reported as unmatched#'
		- '#This message will not be reported as unmatched either#'
		-
			message: '#But this one will be reported#'
			reportUnmatched: true
```

```yaml
parameters:
	reportUnmatchedIgnoredErrors: true
	ignoreErrors:
		- '#This message will be reported as unmatched#'
		- '#This message will be reported as unmatched too#'
		-
			message: '#But this one will not be reported#'
			reportUnmatched: false
```

Excluding whole files
------------------

If your codebase contains some files that are broken on purpose (e. g. to test behaviour of your application on files with invalid PHP code), you can exclude them using the `excludePaths` key. Each entry can either be a file path, a directory path, or a pattern for the [`fnmatch()`](https://www.php.net/manual/en/function.fnmatch.php) function.

If the `excludePaths` entry is a file path or a directory path, but it does not always exist, you can append the path with `(?)` to make it optional. Available since PHPStan 1.11.10.

```yaml
parameters:
	excludePaths:
		- tests/*/data/*
		- src/broken
		- node_modules (?) # optional path, might not exist
```

This is a shortcut for:

```yaml
parameters:
	excludePaths:
	    analyseAndScan:
		    - tests/*/data/*
```

If your project's directory structure mixes your own code (the one you want to analyse and fix bugs in) and third party code (which you're using for [discovering symbols](https://phpstan.org/user-guide/discovering-symbols), but don't want to analyse), the file structure might look like this:

```
├── phpstan.neon
└── src
    ├── foo.php
    ├── ...
    └── thirdparty
        └── bar.php
```

In this case, you want to analyse the whole `src` directory, but want to exclude `src/thirdparty` from analysing. This is how to configure PHPStan:

```yaml
parameters:
    paths:
        - src
    excludePaths:
        analyse:
            - src/thirdparty
```

Additionally, there might be a `src/broken` directory which contains files that you don't want to analyse nor use for discovering symbols. You can modify the configuration to achieve that effect:

```yaml
parameters:
    paths:
        - src
    excludePaths:
        analyse:
            - src/thirdparty
        analyseAndScan:
            - src/broken
```
