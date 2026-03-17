---
title: "ignore.allLineErrors"
shortDescription: "Ignoring all errors on a line is not allowed."
ignorable: true
---

## Code example

In `phpstan.neon`:

```neon
parameters:
	reportIgnoresWithoutComments: true
```

```php
<?php

// @phpstan-ignore-next-line
echo $undefined;

echo $anotherUndefined; // @phpstan-ignore-line
```

## Why is it reported?

When [`reportIgnoresWithoutComments`](/config-reference#reportignoreswithoutcomments) is enabled, PHPStan disallows `@phpstan-ignore-next-line` and `@phpstan-ignore-line` completely. These directives suppress all errors on a line without specifying which error identifiers are being ignored and without requiring an explanation.

This makes it too easy to accidentally suppress unrelated errors that appear on the same line in the future.

## How to fix it

Replace the blanket ignore with `@phpstan-ignore` using a specific error identifier and a comment explaining why:

```diff-php
-// @phpstan-ignore-next-line
+// @phpstan-ignore variable.undefined (used for legacy compatibility)
 echo $undefined;
```

```diff-php
-echo $anotherUndefined; // @phpstan-ignore-line
+echo $anotherUndefined; // @phpstan-ignore variable.undefined (loaded dynamically)
```

Each identifier must have an accompanying comment in parentheses. Learn more about ignoring errors in [Ignoring Errors](/user-guide/ignoring-errors).
