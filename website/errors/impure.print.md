---
title: "impure.print"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @phpstan-pure */
function formatAndPrint(string $name): int
{
	return print 'Hello, ' . $name;
}
```

## Why is it reported?

The `print` language construct is used inside a function or method marked as `@phpstan-pure`. Pure functions must not have side effects -- they should only compute and return a value based on their inputs. Using `print` outputs text to the standard output, which is a side effect.

## How to fix it

Remove the `print` call from the pure function and return the formatted string instead:

```diff-php
 <?php declare(strict_types = 1);

 /** @phpstan-pure */
-function formatAndPrint(string $name): int
+function format(string $name): string
 {
-	return print 'Hello, ' . $name;
+	return 'Hello, ' . $name;
 }
```

Or remove the purity annotation if the function genuinely needs to produce output:

```diff-php
 <?php declare(strict_types = 1);

-/** @phpstan-pure */
 function formatAndPrint(string $name): int
 {
 	return print 'Hello, ' . $name;
 }
```
