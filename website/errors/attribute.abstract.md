---
title: "attribute.abstract"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

#[\Attribute]
abstract class MyAttribute
{
	public function __construct(public string $value)
	{
	}
}

#[MyAttribute('test')]
class Foo
{
}
```

## Why is it reported?

PHP requires attribute classes to be non-abstract classes. An abstract class cannot be used as an attribute because PHP needs to instantiate the attribute when it is applied, and abstract classes cannot be instantiated. Declaring an abstract class with `#[\Attribute]` or using it as an attribute is invalid and will cause a runtime error.

In the example above, `MyAttribute` is declared as an abstract class with the `#[\Attribute]` attribute, which is not allowed.

## How to fix it

Remove the `abstract` keyword from the attribute class:

```diff-php
 <?php declare(strict_types = 1);

 #[\Attribute]
-abstract class MyAttribute
+class MyAttribute
 {
 	public function __construct(public string $value)
 	{
 	}
 }

 #[MyAttribute('test')]
 class Foo
 {
 }
```

If the class needs to share behavior with other attribute classes, use composition or a trait instead of inheritance from an abstract class.
