---
title: "ignore.allLineErrors"
shortDescription: "Ignoring all errors on a line is not allowed."
ignorable: false
autofixable: true
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

Each identifier must have an accompanying comment in parentheses.

Running `vendor/bin/phpstan analyse --fix` replaces `@phpstan-ignore-next-line` and `@phpstan-ignore-line` with `@phpstan-ignore` listing the identifiers of the errors currently reported on the ignored line. Any text after the original tag becomes the comment for each identifier. When the ignored line is in a trait, the fix lists the identifiers reported on that line in the context of every class using the trait. If an error on that line has no identifier, the ignore is left for you to rewrite by hand.

Learn more about ignoring errors in [Ignoring Errors](/user-guide/ignoring-errors).
