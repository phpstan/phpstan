---
title: "property.readOnlyAssignNotInConstructor"
shortDescription: "Readonly property is assigned outside of the constructor."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class User
{
	public readonly string $name;

	public function __construct(string $name)
	{
		$this->name = $name;
	}

	public function rename(string $name): void
	{
		$this->name = $name;
	}
}
```

## Why is it reported?

A `readonly` property is being assigned outside of the class constructor. In PHP, readonly properties can only be initialized once, and this initialization must happen in the constructor of the declaring class (or in `__unserialize`). Assigning to a readonly property in any other method will cause a runtime error.

Assigning the property in a helper method is allowed when that method is only ever run from the constructor: a `private` method (or a `protected` method of a `final` class without a parent) that the class calls on `$this` only from its constructor, directly or through other such methods. The assignment is reported when the method can also be called from elsewhere - from another method, a closure, through a callable, on another object, or when it is not called at all.

## How to fix it

Move the assignment into the constructor, or redesign the class so the readonly property does not need to change after construction:

```diff-php
 class User
 {
-	public readonly string $name;
+	public string $name;

 	public function __construct(string $name)
 	{
 		$this->name = $name;
 	}

 	public function rename(string $name): void
 	{
 		$this->name = $name;
 	}
 }
```

If the initialization logic lives in a separate method, make sure it is only called from the constructor and cannot be called from anywhere else:

```diff-php
 class User
 {
 	public readonly string $name;

 	public function __construct(string $name)
 	{
 		$this->initialize($name);
 	}

-	public function initialize(string $name): void
+	private function initialize(string $name): void
 	{
 		$this->name = $name;
 	}
 }
```

If immutability is desired, create a new instance instead of modifying the existing one:

```diff-php
 class User
 {
 	public function __construct(
 		public readonly string $name,
 	) {}

-	public function rename(string $name): void
+	public function withName(string $name): self
 	{
-		$this->name = $name;
+		return new self($name);
 	}
 }
```
