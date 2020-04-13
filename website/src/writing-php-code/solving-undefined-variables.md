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

This is because the value of `$foo` might change between the two `if` conditions and PHPStan doesn't track this. Instead of the second `if ($foo)` you can use an `isset()` call:

```php
if ($foo) {
    $var = rand();
}

// 200 lines later:

if (isset($var)) {
    echo $var; // OK!
}
```
