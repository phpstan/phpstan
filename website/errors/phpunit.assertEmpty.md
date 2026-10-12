---
title: "phpunit.assertEmpty"
shortDescription: "Calling PHPUnit's assertEmpty() or assertNotEmpty() is disallowed in favour of a more strict assertion."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
	public function testItems(): void
	{
		$items = $this->loadItems();
		$this->assertNotEmpty($items);
	}

	/** @return list<string> */
	private function loadItems(): array
	{
		return ['foo'];
	}
}
```

## Why is it reported?

This rule is part of [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit). It is enabled when [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) is installed and [bleeding edge](/blog/what-is-bleeding-edge) is turned on.

`assertEmpty()` and `assertNotEmpty()` mirror PHP's `empty()` construct, which treats many different values as empty: `null`, `false`, `0`, `0.0`, `''`, `'0'`, and `[]`. An assertion like `assertNotEmpty($value)` therefore passes for values the test probably did not intend to accept, and `assertEmpty($value)` passes for values of the wrong type entirely, such as `'0'` or `false` when an empty array was expected. This makes the test less precise and can hide bugs.

The same reasoning is behind the [`empty.notAllowed`](/error-identifiers/empty.notAllowed) rule from phpstan-strict-rules.

## How to fix it

Replace the assertion with one that checks the exact expected value or type.

In many cases PHPStan can make this change automatically. Run the analysis with the `--fix` option to rewrite the call based on the native type of the asserted value:

| Native type of the value | `assertEmpty()` becomes | `assertNotEmpty()` becomes |
|---|---|---|
| `bool` | `assertFalse($value)` | `assertTrue($value)` |
| `array` | `assertCount(0, $value)` | `assertNotCount(0, $value)` |
| `int` | `assertSame(0, $value)` | `assertNotSame(0, $value)` |
| nullable final class that is not `Countable` | `assertNull($value)` | `assertNotNull($value)` |

Calls that use named or unpacked arguments, or that assert values of other types, have to be fixed by hand.

For arrays and countable values, use `assertCount()` or compare against an empty array:

```diff-php
 <?php declare(strict_types = 1);

 use PHPUnit\Framework\TestCase;

 class MyTest extends TestCase
 {
 	public function testItems(): void
 	{
 		$items = $this->loadItems();
-		$this->assertNotEmpty($items);
+		$this->assertCount(1, $items);
 	}
 }
```

```diff-php
-$this->assertEmpty($items);
+$this->assertSame([], $items);
```

For strings, compare against the empty string:

```diff-php
-$this->assertEmpty($name);
+$this->assertSame('', $name);
```

```diff-php
-$this->assertNotEmpty($name);
+$this->assertNotSame('', $name);
```

For nullable values, use `assertNull()` or `assertNotNull()`:

```diff-php
-$this->assertNotEmpty($user);
+$this->assertNotNull($user);
```
