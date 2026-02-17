---
title: "property.notReadable"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

abstract class Base
{
	abstract public string $name { get; set; }
}

class Child extends Base
{
	public string $name { // ERROR: Property Child::$name overriding readable property Base::$name also has to be readable.
		set => $value;
	}
}
```

## Why is it reported?

When a child class overrides a property from a parent class or interface, it must preserve the readability contract. If the parent property is readable (has a `get` hook or is a regular property), the overriding property must also be readable. Removing readability from an overriding property would break the [Liskov Substitution Principle](https://en.wikipedia.org/wiki/Liskov_substitution_principle) -- code that expects to read the property based on the parent type would fail.

This rule applies to PHP 8.4+ property hooks.

## How to fix it

Add a `get` hook to the overriding property so it remains readable:

```diff-php
 <?php declare(strict_types = 1);

 abstract class Base
 {
 	abstract public string $name { get; set; }
 }

 class Child extends Base
 {
 	public string $name {
+		get => $this->name;
 		set => $value;
 	}
 }
```
