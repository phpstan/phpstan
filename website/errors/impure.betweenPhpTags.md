---
title: "impure.betweenPhpTags"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @phpstan-pure */
function doFoo(): int
{
	?><p>Hello</p><?php
	return 1;
}
```

## Why is it reported?

A function marked as `@phpstan-pure` contains HTML output between PHP opening and closing tags. Pure functions must not have side effects, and outputting HTML to the browser is a side effect.

## How to fix it

Remove the HTML output from the pure function:

```diff-php
 <?php declare(strict_types = 1);

 /** @phpstan-pure */
 function doFoo(): int
 {
-	?><p>Hello</p><?php
 	return 1;
 }
```

Or remove the `@phpstan-pure` annotation if the function intentionally produces output.
