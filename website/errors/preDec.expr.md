---
title: "preDec.expr"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): int
{
	return 5;
}

--doFoo();
```

## Why is it reported?

The decrement operator (`--`) is applied to an expression that is not a variable. The `--` operator requires an assignable expression (a variable, array element, or property). In the example above, `doFoo()` is a function call result, which cannot be decremented.

## How to fix it

Apply the operator to a variable instead:

```diff-php
 <?php declare(strict_types = 1);

---doFoo();
+$value = doFoo();
+--$value;
```
