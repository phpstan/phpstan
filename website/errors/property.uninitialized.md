---
title: "property.uninitialized"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	private string $name;

	public function getName(): string
	{
		return $this->name;
	}
}
```

## Why is it reported?

The class has a typed property that is never assigned a value -- neither as a default value nor in the constructor. Accessing an uninitialized typed property in PHP causes a fatal error (`Typed property must not be accessed before initialization`).

This rule does not apply to `readonly` properties, which have their own dedicated checks.

## How to fix it

Assign a default value to the property:

```diff-php
 class Foo
 {
-	private string $name;
+	private string $name = '';
 }
```

Or initialize the property in the constructor:

```diff-php
 class Foo
 {
 	private string $name;

+	public function __construct(string $name)
+	{
+		$this->name = $name;
+	}
+
 	public function getName(): string
 	{
 		return $this->name;
 	}
 }
```
