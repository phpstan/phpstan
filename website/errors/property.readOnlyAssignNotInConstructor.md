---
title: "property.readOnlyAssignNotInConstructor"
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
