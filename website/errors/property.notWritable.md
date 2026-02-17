---
title: "property.notWritable"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class ParentClass
{
	public string $name;
}

class ChildClass extends ParentClass
{
	public string $name {
		get => 'fixed';
	}
}
```

## Why is it reported?

A child class overrides a writable property from a parent class but makes it non-writable. In the example above, `ParentClass::$name` is writable, but `ChildClass::$name` only defines a `get` hook without a `set` hook, making it read-only. This violates the property contract established by the parent class.

## How to fix it

Ensure the overriding property also supports writes:

```diff-php
 <?php declare(strict_types = 1);

 class ChildClass extends ParentClass
 {
 	public string $name {
 		get => 'fixed';
+		set => $this->name = $value;
 	}
 }
```
