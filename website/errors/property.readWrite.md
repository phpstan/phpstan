---
title: "property.readWrite"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Base
{
	public readonly string $name;

	public function __construct(string $name)
	{
		$this->name = $name;
	}
}

class Child extends Base
{
	public string $name; // ERROR: Readwrite property Child::$name overrides readonly property Base::$name.

	public function __construct(string $name)
	{
		$this->name = $name;
	}
}
```

## Why is it reported?

A child class declares a readwrite (non-readonly) property that overrides a `readonly` property from the parent class. This violates the contract established by the parent class -- the parent guarantees that the property cannot be modified after initialization, but the child removes this guarantee. This breaks the [Liskov Substitution Principle](https://en.wikipedia.org/wiki/Liskov_substitution_principle) because code relying on the parent type expects the property to be immutable.

## How to fix it

Keep the `readonly` modifier on the overriding property:

```diff-php
 <?php declare(strict_types = 1);

 class Child extends Base
 {
-	public string $name;
+	public readonly string $name;

 	public function __construct(string $name)
 	{
 		$this->name = $name;
 	}
 }
```
