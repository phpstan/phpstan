---
title: "ignore.count"
ignorable: true
---

## Code example

In `phpstan.neon`:

```neon
parameters:
	ignoreErrors:
		-
			message: '#Call to method Foo::bar\(\)#'
			path: src/Foo.php
			count: 1
```

This error is reported when the actual number of occurrences does not match the expected `count`.

## Why is it reported?

The ignored error pattern was expected to match a specific number of times (`count`), but occurred either more or fewer times than expected. For example, if `count: 1` is specified but the error occurs 3 times, this identifier is reported.

This typically happens after code changes -- a refactoring might introduce new instances of the error, or fix some of the existing ones, causing the count to drift from the configured value.

## How to fix it

Update the `count` to match the actual number of occurrences:

```diff-neon
 parameters:
 	ignoreErrors:
 		-
 			message: '#Call to method Foo::bar\(\)#'
 			path: src/Foo.php
-			count: 1
+			count: 3
```

Or remove the `count` parameter to ignore all occurrences of the error:

```diff-neon
 parameters:
 	ignoreErrors:
 		-
 			message: '#Call to method Foo::bar\(\)#'
 			path: src/Foo.php
-			count: 1
```

If the ignored error no longer applies, remove the entry entirely. Learn more about ignoring errors in [Ignoring Errors](/user-guide/ignoring-errors).
