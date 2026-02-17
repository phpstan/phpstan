---
title: "property.invalidPromoted"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doSomething(public string $name): void // ERROR: Promoted properties can be in constructor only.
	{
	}
}
```

## Why is it reported?

Promoted properties (parameters with visibility modifiers like `public`, `protected`, or `private`) can only be used in a class constructor. PHP does not allow promoted properties in regular methods, abstract constructors, or as variadic parameters.

This error is reported for several invalid uses of promoted properties:
- Using promoted properties in a method other than `__construct`
- Using promoted properties in an abstract constructor (which has no body to perform the assignment)
- Using a variadic parameter as a promoted property

## How to fix it

If the property promotion is used in a non-constructor method, move it to the constructor or declare the property separately:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public function doSomething(public string $name): void
+	public string $name;
+
+	public function doSomething(string $name): void
 	{
+		$this->name = $name;
 	}
 }
```

If the promoted property is variadic, declare the property separately and accept the variadic parameter normally:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
+	/** @var list<string> */
+	public array $names;
+
-	public function __construct(public string ...$names)
+	public function __construct(string ...$names)
 	{
+		$this->names = $names;
 	}
 }
```
