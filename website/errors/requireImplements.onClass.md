---
title: "requireImplements.onClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface HasName
{
	public function getName(): string;
}

/**
 * @phpstan-require-implements HasName
 */
class MyClass
{
}
```

## Why is it reported?

The `@phpstan-require-implements` PHPDoc tag is only valid on traits. It tells PHPStan that any class using the trait must implement the specified interface. Placing this tag on a class or an enum has no effect and indicates a misunderstanding of the tag's purpose.

## How to fix it

Move the tag to a trait:

```diff-php
 /**
  * @phpstan-require-implements HasName
  */
-class MyClass
+trait MyTrait
 {
 }
```

Or if the class should implement the interface, use `implements` directly:

```diff-php
-/**
- * @phpstan-require-implements HasName
- */
-class MyClass
+class MyClass implements HasName
 {
+	public function getName(): string
+	{
+		return 'name';
+	}
 }
```
