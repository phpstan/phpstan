---
title: "nullableType.never"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function bar(string $a): ?never // error: Type never cannot be part of a nullable type declaration.
	{
		throw new \RuntimeException($a);
	}
}
```

## Why is it reported?

The `never` return type indicates that a function never returns normally -- it either always throws an exception or terminates execution. A nullable `never` type (`?never`) is a contradiction: if a function could return `null`, it does return, which conflicts with the meaning of `never`. PHP does not allow `never` to be part of a union or nullable type declaration.

## How to fix it

If the function always throws or exits, use `never` without the nullable modifier.

```diff-php
 class Foo
 {
-	public function bar(string $a): ?never
+	public function bar(string $a): never
 	{
 		throw new \RuntimeException($a);
 	}
 }
```

If the function can sometimes return `null`, use `void` instead since it indicates the function returns no meaningful value.

```diff-php
 class Foo
 {
-	public function bar(string $a): ?never
+	public function bar(string $a): void
 	{
 		if ($a === '') {
 			throw new \RuntimeException('Empty string');
 		}
 	}
 }
```
