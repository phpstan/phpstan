---
title: "property.notFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public string $name = 'hello';
}

$foo = new Foo();
echo $foo->surname; // ERROR: Access to an undefined property Foo::$surname.
```

## Why is it reported?

The code accesses a property that does not exist on the object. This typically indicates a typo in the property name, a missing property declaration, or accessing a property on a wrong type. At runtime this would trigger a deprecation notice (or an error in strict scenarios) and return `null`.

## How to fix it

There are many ways to solve this error. Read the comprehensive article [Solving PHPStan error "Access to an undefined property"](/blog/solving-phpstan-access-to-undefined-property) for all of them, including:

* Fixing the property name if it's a typo
* [Narrowing the type](/writing-php-code/narrowing-types) the property is accessed on
* Declaring the missing property on the class
* Adding `#[AllowDynamicProperties]` attribute (PHP 8.2+)
* Configuring [`universalObjectCratesClasses`](/config-reference#universal-object-crates) for classes without predefined structure
* Adding `@property` PHPDoc tags for magic properties
* Using `@mixin` PHPDoc tag for delegated property access
* Using [framework-specific extensions](/user-guide/extension-library)
* Writing a custom [class reflection extension](/developing-extensions/class-reflection-extensions)
