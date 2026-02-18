---
title: "property.tooWideBool"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	private bool $active; // ERROR: Property Foo::$active (bool) is never assigned false so the property type can be changed to true.

	public function __construct()
	{
		$this->active = true;
	}

	public function activate(): void
	{
		$this->active = true;
	}
}
```

## Why is it reported?

The property is declared with type `bool`, but PHPStan determined that only one of the two boolean values (`true` or `false`) is ever assigned to it. This means the type is wider than necessary. Having a more precise type helps catch bugs where the other boolean value is unexpectedly used.

## How to fix it

Narrow the property type to the specific boolean value that is actually assigned:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	private bool $active;
+	private true $active;

 	public function __construct()
 	{
 		$this->active = true;
 	}
 }
```

Alternatively, if the property should support both `true` and `false`, add the missing assignment:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	private bool $active;

 	public function __construct()
 	{
 		$this->active = true;
 	}

+	public function deactivate(): void
+	{
+		$this->active = false;
+	}
 }
```
