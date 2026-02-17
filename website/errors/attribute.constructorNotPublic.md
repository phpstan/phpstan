---
title: "attribute.constructorNotPublic"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

#[\Attribute]
class MyAttribute
{
	private function __construct(public string $name)
	{
	}
}

#[MyAttribute('test')]
class Foo
{
}
```

## Why is it reported?

PHP requires the constructor of an attribute class to be public. When an attribute is applied, PHP internally instantiates the attribute class, and it can only do so if the constructor is accessible. A non-public constructor (private or protected) will cause a runtime error when the attribute is used.

In the example above, `MyAttribute` has a `private` constructor, so using `#[MyAttribute('test')]` will fail.

## How to fix it

Change the attribute class constructor visibility to `public`:

```diff-php
 <?php declare(strict_types = 1);

 #[\Attribute]
 class MyAttribute
 {
-	private function __construct(public string $name)
+	public function __construct(public string $name)
 	{
 	}
 }

 #[MyAttribute('test')]
 class Foo
 {
 }
```
