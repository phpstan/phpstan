---
title: "ignore.unmatchedLine"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): int
{
	// @phpstan-ignore-next-line
	return $i + 1;
}
```

## Why is it reported?

A `@phpstan-ignore-next-line` or `@phpstan-ignore-line` comment is present, but no error is reported on that line. This means the ignore comment is unnecessary because the code on that line does not produce any PHPStan error.

This typically happens when:
- The error that was originally being suppressed has been fixed but the ignore comment was not removed
- Code was refactored and the error moved to a different line
- PHPStan was updated and the rule no longer reports an error in this context

This error is controlled by the [`reportUnmatchedIgnoredErrors`](/config-reference#reportunmatchedignorederrors) configuration parameter.

## How to fix it

Remove the unnecessary ignore comment:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): int
 {
-	// @phpstan-ignore-next-line
 	return $i + 1;
 }
```

If the ignore comment was suppressing an error that still exists but has moved, relocate the comment to the correct line.
