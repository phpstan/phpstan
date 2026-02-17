---
title: "new.void"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Logger
{
	public function __construct()
	{
	}
}

function getLogger(): Logger
{
	$result = new Logger();
	echo $result;
	return $result;
}
```

## Why is it reported?

The result of a `new` expression is being used in a context where it evaluates to void. This typically occurs when PHPStan determines that the constructor call produces a void result in the current context, for example when the `new` expression is used inside an attribute declaration or another specialized context where the return value is not meaningful.

## How to fix it

Ensure the result of the `new` expression is not used as a value when it is not expected to produce one. If the object is needed, assign it to a variable and use it separately:

```diff-php
 <?php declare(strict_types = 1);

-echo new Logger();
+$logger = new Logger();
```
