---
title: "unionType.void"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function bar(): void|string
	{
		return 'hello';
	}
}
```

## Why is it reported?

The `void` type cannot be part of a union type declaration. In PHP, `void` is a standalone type that indicates a function or method does not return a value. Combining `void` with other types in a union contradicts its meaning -- if the function can return a `string`, it is not "void."

This is a PHP language restriction enforced at the parser level.

## How to fix it

Remove `void` from the union type. If the method can return a value, use only the types it can actually return:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public function bar(): void|string
+	public function bar(): string
 	{
 		return 'hello';
 	}
 }
```

If the method does not always return a value, use a nullable type instead:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public function bar(): void|string
+	public function bar(): ?string
 	{
 		// ...
 	}
 }
```

If the method truly never returns a value, use `void` as the sole return type:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public function bar(): void|string
+	public function bar(): void
 	{
-		return 'hello';
+		// perform side effects only
 	}
 }
```
