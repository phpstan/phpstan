---
title: "function.resultUnused"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(string $url): void
{
	sprintf('%s/path', $url);
}
```

## Why is it reported?

The function is called on its own line as a statement, but it has no side effects -- it only computes and returns a value. Since the return value is not assigned to a variable, passed as an argument, or otherwise used, the call achieves nothing. This usually indicates that the developer forgot to use the return value or intended to call a different function.

Common examples include `sprintf()` (which returns a string but does not output anything, unlike `printf()`) and other pure functions whose results are discarded.

## How to fix it

Use the return value:

```diff-php
-	sprintf('%s/path', $url);
+	$result = sprintf('%s/path', $url);
```

Or call the function that produces the intended side effect:

```diff-php
-	sprintf('%s/path', $url);
+	printf('%s/path', $url);
```
