---
title: "possiblyImpure.echo"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @phpstan-pure */
function formatAndPrint(string $name): string
{
	$result = 'Hello, ' . $name;
	echo $result;
	return $result;
}
```

## Why is it reported?

The function or method is marked as `@phpstan-pure`, but it contains an `echo` statement. Pure functions must not have side effects -- they should only compute and return a value based on their input parameters. Outputting text with `echo` is a side effect because it modifies the program's output stream.

## How to fix it

Remove the `echo` statement from the pure function and let the caller handle the output:

```diff-php
 <?php declare(strict_types = 1);

 /** @phpstan-pure */
 function formatAndPrint(string $name): string
 {
 	$result = 'Hello, ' . $name;
-	echo $result;
 	return $result;
 }
+
+echo formatAndPrint('World');
```

Alternatively, if the function needs to produce output, remove the `@phpstan-pure` annotation:

```diff-php
 <?php declare(strict_types = 1);

-/** @phpstan-pure */
 function formatAndPrint(string $name): string
 {
 	$result = 'Hello, ' . $name;
 	echo $result;
 	return $result;
 }
```
