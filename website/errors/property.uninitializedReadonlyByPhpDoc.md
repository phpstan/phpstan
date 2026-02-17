---
title: "property.uninitializedReadonlyByPhpDoc"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class User
{
	/** @readonly */
	public string $name; // ERROR: Class User has an uninitialized @readonly property $name. Assign it in the constructor.

	public function __construct()
	{
	}
}
```

## Why is it reported?

A property marked with the `@readonly` PHPDoc tag is not assigned a value in the constructor. Properties annotated as `@readonly` should be initialized in the constructor (or via a default value) because they cannot be assigned later. Leaving a `@readonly` property uninitialized means it will never receive a value, which defeats the purpose of the property.

This rule also reports access to an uninitialized `@readonly` property before it has been assigned in the constructor.

## How to fix it

Assign the property in the constructor:

```diff-php
 <?php declare(strict_types = 1);

 class User
 {
 	/** @readonly */
 	public string $name;

-	public function __construct()
+	public function __construct(string $name)
 	{
+		$this->name = $name;
 	}
 }
```

Or provide a default value:

```diff-php
 <?php declare(strict_types = 1);

 class User
 {
 	/** @readonly */
-	public string $name;
+	public string $name = 'default';

 	public function __construct()
 	{
 	}
 }
```

If the property should not be `@readonly`, remove the annotation:

```diff-php
 <?php declare(strict_types = 1);

 class User
 {
-	/** @readonly */
 	public string $name;

 	public function __construct()
 	{
 	}
 }
```
