---
title: "notEqual.notAllowed"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$result = 123 != 456; // error: Loose comparison via "!=" is not allowed.
```

## Why is it reported?

Loose comparison (`!=`) uses PHP's type juggling rules to compare values, which can produce surprising and hard-to-predict results. For example, `"0" != ""` evaluates to `true`, but `0 != ""` evaluates to `false`. These implicit type conversions are a common source of bugs.

This error is reported by [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules).

## How to fix it

Use strict comparison (`!==`) instead, which compares both the value and the type without implicit conversion.

```diff-php
-$result = 123 != 456;
+$result = 123 !== 456;
```

If the values have different types and a comparison is still needed, cast one operand explicitly to make the intent clear.

```diff-php
-if ($userInput != 0) {
+if ((int) $userInput !== 0) {
 	// ...
 }
```
