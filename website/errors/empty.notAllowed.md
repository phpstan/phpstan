---
title: "empty.notAllowed"
shortDescription: "Usage of empty() or PHPUnit's assertEmpty() and assertNotEmpty() is disallowed by strict rules."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function isValid(string $name): bool
{
	return !empty($name);
}
```

This rule is provided by the package [`phpstan/phpstan-strict-rules`](https://github.com/phpstan/phpstan-strict-rules).

The same identifier is also reported by [`phpstan/phpstan-phpunit`](https://github.com/phpstan/phpstan-phpunit) for PHPUnit's `assertEmpty()` and `assertNotEmpty()` assertions, when `phpstan/phpstan-strict-rules` is installed and [Bleeding Edge](/blog/what-is-bleeding-edge) is enabled:

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
	/** @param list<string> $items */
	public function testItems(array $items): void
	{
		$this->assertNotEmpty($items);
	}
}
```

## Why is it reported?

The `empty()` language construct is disallowed by strict rules because it combines two checks into one -- it checks if a variable is set and if its value is falsy. This makes its behaviour unpredictable with different types: `empty('0')` returns `true`, `empty(0)` returns `true`, and `empty([])` returns `true`. These implicit coercions can hide bugs.

Using explicit comparisons makes the code's intent clearer and avoids surprising results from PHP's loose type juggling.

PHPUnit's `assertEmpty()` and `assertNotEmpty()` have the same semantics as `empty()`, so a test using them passes for any falsy value -- `assertEmpty()` succeeds for `null`, `false`, `0`, `''`, `'0'`, and `[]` alike. Such a test does not verify what the value actually is. Error message: `assertEmpty() is not allowed. Use more strict assertion.`

## How to fix it

Replace `empty()` with an explicit comparison appropriate for the expected type:

```diff-php
 <?php declare(strict_types = 1);

 function isValid(string $name): bool
 {
-	return !empty($name);
+	return $name !== '';
 }
```

For arrays, compare against an empty array or use `count()`:

```diff-php
 <?php declare(strict_types = 1);

 /** @param list<string> $items */
 function hasItems(array $items): bool
 {
-	return !empty($items);
+	return count($items) > 0;
 }
```

In PHPUnit tests, replace `assertEmpty()` and `assertNotEmpty()` with an assertion that checks the exact expected value:

```diff-php
 <?php declare(strict_types = 1);

 use PHPUnit\Framework\TestCase;

 class MyTest extends TestCase
 {
 	/** @param list<string> $items */
 	public function testItems(array $items): void
 	{
-		$this->assertNotEmpty($items);
+		$this->assertNotSame([], $items);
 	}
 }
```

Other strict alternatives include `assertSame([], $value)`, `assertCount(0, $value)`, `assertSame('', $value)`, or `assertNull($value)`, depending on the expected type.
