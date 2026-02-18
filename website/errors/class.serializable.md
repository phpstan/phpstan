---
title: "class.serializable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo implements \Serializable
{
	public function serialize(): ?string
	{
		return serialize([]);
	}

	public function unserialize(string $data): void
	{
	}
}
```

## Why is it reported?

A non-abstract class implements the `Serializable` interface but does not implement the `__serialize()` and/or `__unserialize()` magic methods. Since PHP 8.1, the `Serializable` interface is deprecated in favour of the `__serialize()` and `__unserialize()` magic methods. Classes that implement `Serializable` without also providing the magic methods will emit a deprecation notice.

## How to fix it

Add the `__serialize()` and `__unserialize()` magic methods:

```diff-php
 <?php declare(strict_types = 1);

 class Foo implements \Serializable
 {
 	public function serialize(): ?string
 	{
 		return serialize([]);
 	}

 	public function unserialize(string $data): void
 	{
 	}

+	public function __serialize(): array
+	{
+		return [];
+	}
+
+	public function __unserialize(array $data): void
+	{
+	}
 }
```

Or better yet, remove the `Serializable` interface entirely and rely solely on the magic methods:

```diff-php
 <?php declare(strict_types = 1);

-class Foo implements \Serializable
+class Foo
 {
-	public function serialize(): ?string
-	{
-		return serialize([]);
-	}
-
-	public function unserialize(string $data): void
-	{
-	}
-
 	public function __serialize(): array
 	{
 		return [];
 	}

 	public function __unserialize(array $data): void
 	{
 	}
 }
```
