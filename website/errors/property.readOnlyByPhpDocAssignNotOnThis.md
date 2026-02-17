---
title: "property.readOnlyByPhpDocAssignNotOnThis"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/** @readonly */
	public int $value;

	public function __construct(self $other)
	{
		$other->value = 10; // ERROR: @readonly property Foo::$value is not assigned on $this.
	}
}
```

## Why is it reported?

The property is marked as `@readonly` in its PHPDoc, which means it should only be assigned once during initialization. While PHPStan allows `@readonly` properties to be assigned inside the constructor of the declaring class, the assignment must be on `$this` -- not on another instance of the same class. Assigning a `@readonly` property on a different object instance violates the readonly contract because it modifies state that may have already been initialized.

## How to fix it

Assign the `@readonly` property on `$this` inside the constructor:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	/** @readonly */
 	public int $value;

-	public function __construct(self $other)
+	public function __construct(int $value)
 	{
-		$other->value = 10;
+		$this->value = $value;
 	}
 }
```

Alternatively, if the property should be writable on different instances, remove the `@readonly` annotation:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	/** @readonly */
 	public int $value;

 	public function __construct(self $other)
 	{
 		$other->value = 10;
 	}
 }
```
