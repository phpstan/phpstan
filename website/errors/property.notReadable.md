---
title: "property.notReadable"
shortDescription: "Overriding property removes readability required by the parent."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface HasValue
{
	public int $value { get; }
}

class Foo implements HasValue
{
	private int $stored = 0;

	public int $value {
		set {
			$this->stored = $value;
		}
	}
}
```

## Why is it reported?

When a child class overrides a property from a parent class or interface, it must preserve the readability contract. If the parent property is readable (has a `get` hook or is a regular property), the overriding property must also be readable. Removing readability from an overriding property would break the [Liskov Substitution Principle](https://en.wikipedia.org/wiki/Liskov_substitution_principle) -- code that expects to read the property based on the parent type would fail.

In the example above, the interface `HasValue` declares `$value` as readable (`get`), but the implementing class defines only a `set` hook that stores to a different field. This makes the property virtual and write-only, violating the readability contract.

This rule applies to PHP 8.4+ property hooks.

## How to fix it

Add a `get` hook to the overriding property so it remains readable:

```diff-php
 class Foo implements HasValue
 {
 	private int $stored = 0;

 	public int $value {
+		get {
+			return $this->stored;
+		}
 		set {
 			$this->stored = $value;
 		}
 	}
 }
```
