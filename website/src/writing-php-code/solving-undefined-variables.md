---
title: Solving Undefined Variables
---

Consider this example:

```php
if (somethingIsTrue()) {
	$foo = true;
} elseif (orSomethingElseIsTrue()) {
	$foo = false;
} else {
	$this->redirect('homepage');
}

doFoo($foo); // possibly undefined variable $foo
```

Without any special knowledge we might think that the variable `$foo` might be undefined in case the `else` branch was executed. However, some specific method calls can be perceived by project developers also as early terminating - like a `redirect()` that stops execution by throwing an internal exception.

These methods can be configured by specifying a class on whose instance they are called using `earlyTerminatingMethodCalls` option key in the [configuration file](/config-reference) like this:

```yaml
parameters:
	earlyTerminatingMethodCalls:
		Nette\Application\UI\Presenter:
			- redirect
			- redirectUrl
			- sendJson
			- sendResponse
```

The same applies to plain global functions. Early terminating functions can be defined using the `earlyTerminatingFunctionCalls` key, like this one with a global helper function `redirect()`:

```yaml
parameters:
	earlyTerminatingFunctionCalls:
		- redirect
```

PHPDoc tag `@return never` above a function or a method can be used instead of configuring `earlyTerminatingFunctionCalls` or `earlyTerminatingMethodCalls`.

---------

Another issue you might encounter is that PHPStan doesn't understand conditionally defined variables like this:

```php
if ($foo) {
    $var = rand();
}

// 200 lines later:

if ($foo) {
    echo $var; // Variable $var might not be defined.
}
```

Fortunately this has been understood by PHPStan since version [0.12.64](https://github.com/phpstan/phpstan/releases/tag/0.12.64). So if you're still experiencing this problem, make sure to upgrade to the latest version.
