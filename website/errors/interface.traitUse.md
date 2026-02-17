---
title: "interface.traitUse"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

trait Timestampable
{
	public function getCreatedAt(): string
	{
		return '2024-01-01';
	}
}

interface EntityInterface
{
	use Timestampable;
}
```

## Why is it reported?

Interfaces cannot use traits in PHP. Traits provide concrete method implementations, while interfaces are meant to define a contract of method signatures without implementations. Using a trait inside an interface is a language-level error that will cause a fatal error at runtime.

## How to fix it

Declare the required methods directly in the interface as abstract method signatures:

```diff-php
 <?php declare(strict_types = 1);

 interface EntityInterface
 {
-	use Timestampable;
+	public function getCreatedAt(): string;
 }
```

If the trait's functionality needs to be shared, use the trait in the implementing classes instead:

```diff-php
 <?php declare(strict_types = 1);

 interface EntityInterface
 {
-	use Timestampable;
+	public function getCreatedAt(): string;
+}
+
+class Entity implements EntityInterface
+{
+	use Timestampable;
 }
```
