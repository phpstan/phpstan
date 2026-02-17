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

Fix the property name if it is a typo:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public string $name = 'hello';
 }

 $foo = new Foo();
-echo $foo->surname;
+echo $foo->name;
```

Or declare the missing property on the class:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public string $name = 'hello';
+	public string $surname = '';
 }

 $foo = new Foo();
 echo $foo->surname;
```

If the class uses magic properties via `__get`/`__set`, document them with `@property` PHPDoc tags:

```diff-php
 <?php declare(strict_types = 1);

+/** @property string $surname */
 class Foo
 {
 	public string $name = 'hello';

 	public function __get(string $name): mixed { /* ... */ }
 }
```
