---
title: "ignore.noComment"
shortDescription: "Ignore with identifier has no comment."
ignorable: false
---

## Code example

In `phpstan.neon`:

```neon
parameters:
	reportIgnoresWithoutComments: true
```

```php
<?php

// @phpstan-ignore variable.undefined
echo $undefined;
```

## Why is it reported?

When [`reportIgnoresWithoutComments`](/config-reference#reportignoreswithoutcomments) is enabled, every `@phpstan-ignore` identifier must have an accompanying comment in parentheses explaining why the error is being ignored.

This ensures that ignored errors are documented and reviewable, making it clear to other developers (and your future self) why each suppression exists.

## How to fix it

Add a comment in parentheses after the identifier:

```diff-php
-// @phpstan-ignore variable.undefined
+// @phpstan-ignore variable.undefined (not available at this point)
 echo $undefined;
```

When ignoring multiple identifiers on the same line, each one needs its own comment:

```diff-php
-echo $foo; // @phpstan-ignore variable.undefined, wrong.id
+echo $foo; // @phpstan-ignore variable.undefined (reason), wrong.id (reason)
```

Learn more about ignoring errors in [Ignoring Errors](/user-guide/ignoring-errors).
