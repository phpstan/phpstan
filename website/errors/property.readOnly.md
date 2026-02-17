---
title: "property.readOnly"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Base
{
	public mixed $value;
}

class Child extends Base
{
	public readonly mixed $value;
}
```

## Why is it reported?

A `readonly` property in a child class is overriding a readwrite property in the parent class. The parent class allows both reading and writing, but the child class restricts writing. This violates the Liskov Substitution Principle because code written against the parent type expects to be able to assign the property.

## How to fix it

Remove the `readonly` modifier from the child property to match the parent's readwrite contract:

```diff-php
 <?php declare(strict_types = 1);

 class Base
 {
 	public mixed $value;
 }

 class Child extends Base
 {
-	public readonly mixed $value;
+	public mixed $value;
 }
```

Alternatively, if both should be readonly, add the `readonly` modifier to the parent property as well:

```diff-php
 <?php declare(strict_types = 1);

 class Base
 {
-	public mixed $value;
+	public readonly mixed $value;
 }

 class Child extends Base
 {
 	public readonly mixed $value;
 }
```
