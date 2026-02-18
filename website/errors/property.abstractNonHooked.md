---
title: "property.abstractNonHooked"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

abstract class Person
{
	abstract public string $name;
}
```

## Why is it reported?

In PHP 8.4+, only hooked properties can be declared abstract. A property without hooks has no behaviour that subclasses need to implement, so making it abstract does not make sense. PHP rejects this at compile time.

## How to fix it

Add hooks to the property to make it abstract:

```diff-php
 abstract class Person
 {
-	abstract public string $name;
+	abstract public string $name { get; set; }
 }
```

Or remove the `abstract` modifier and use a regular property:

```diff-php
 abstract class Person
 {
-	abstract public string $name;
+	public string $name;
 }
```
