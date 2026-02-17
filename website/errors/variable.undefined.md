---
title: "variable.undefined"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function greet(): string
{
	return 'Hello, ' . $name;
}
```

## Why is it reported?

The variable being used has not been defined in the current scope. This usually means the variable name is misspelled, the variable was intended to be a parameter, or the code path that assigns the variable is not always reached.

## How to fix it

Make sure the variable is defined before it is used.

```diff-php
 <?php declare(strict_types = 1);

-function greet(): string
+function greet(string $name): string
 {
 	return 'Hello, ' . $name;
 }
```

If the variable might not be defined depending on the code path, assign a default value:

```diff-php
 <?php declare(strict_types = 1);

 function summarize(bool $verbose): string
 {
+	$detail = '';
 	if ($verbose) {
 		$detail = 'Full details here';
 	}

 	return $detail;
 }
```
