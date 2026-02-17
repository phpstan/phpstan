---
title: "method.tentativeReturnType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class MyIterator implements Iterator
{
	public function current(): void
	{
	}

	public function next(): void
	{
	}

	public function key(): void
	{
	}

	public function valid(): void
	{
	}

	public function rewind(): void
	{
	}
}
```

## Why is it reported?

The return type of the method is not covariant with the tentative return type declared by the parent method in a PHP built-in class. Since PHP 8.1, many built-in methods have "tentative return types" that signal what the return type will become mandatory in a future PHP version. If your overriding method declares an incompatible return type, PHP emits a deprecation notice.

The error message includes a tip: "Make it covariant, or use the `#[\ReturnTypeWillChange]` attribute to temporarily suppress the error."

## How to fix it

Declare a return type that is covariant (the same type or a subtype) with the tentative return type of the parent method.

```diff-php
 <?php declare(strict_types = 1);

 class MyIterator implements Iterator
 {
-	public function current(): void
+	public function current(): mixed
 	{
+		return null;
 	}

 	public function next(): void
 	{
 	}

-	public function key(): void
+	public function key(): mixed
 	{
+		return null;
 	}

-	public function valid(): void
+	public function valid(): bool
 	{
+		return false;
 	}

 	public function rewind(): void
 	{
 	}
 }
```

If you need to support older PHP versions that do not allow these return types, you can temporarily use the `#[\ReturnTypeWillChange]` attribute:

```diff-php
 <?php declare(strict_types = 1);

 class MyIterator implements Iterator
 {
+	#[\ReturnTypeWillChange]
 	public function current()
 	{
+		return null;
 	}

 	// ...
 }
```
