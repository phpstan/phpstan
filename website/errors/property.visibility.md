---
title: "property.visibility"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Base
{
	public string $name = 'hello';
}

class Child extends Base
{
	protected string $name = 'world'; // ERROR: Protected property Child::$name overriding public property Base::$name should also be public.
}
```

## Why is it reported?

When a child class overrides a property from a parent class, it must not reduce the visibility of the property. A `public` property cannot become `protected` or `private`, and a `protected` property cannot become `private`. Reducing visibility violates the [Liskov Substitution Principle](https://en.wikipedia.org/wiki/Liskov_substitution_principle) -- code that accesses the property through the parent type expects the declared visibility level to be maintained.

## How to fix it

Match or widen the visibility of the overriding property:

```diff-php
 <?php declare(strict_types = 1);

 class Base
 {
 	public string $name = 'hello';
 }

 class Child extends Base
 {
-	protected string $name = 'world';
+	public string $name = 'world';
 }
```
