---
title: "possiblyImpure.betweenPhpTags"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Formatter
{
	/**
	 * @phpstan-pure
	 */
	public function format(string $value): string
	{
		?>Some HTML output<?php
		return strtoupper($value);
	}
}
```

## Why is it reported?

The function or method is marked as `@phpstan-pure` but contains inline HTML between PHP closing (`?>`) and opening (`<?php`) tags. Outputting HTML to the browser is a side effect -- it changes the state of the output buffer. Pure functions must not have side effects; they should only compute and return a value based on their inputs.

## How to fix it

Remove the inline HTML output from the pure function, or remove the `@phpstan-pure` annotation if the function genuinely needs to produce output:

```diff-php
 class Formatter
 {
 	/**
-	 * @phpstan-pure
+	 * @phpstan-impure
 	 */
 	public function format(string $value): string
 	{
 		?>Some HTML output<?php
 		return strtoupper($value);
 	}
 }
```
