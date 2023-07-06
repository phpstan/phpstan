---
title: 'Solving PHPStan error "Access to an undefined property"'
date: 2023-03-31
tags: guides
---

"Access to an undefined property `Foo::$x`" is one of the most common errors you will encounter when running PHPStan. Here are all the ways you can solve it.

Fix the name
----------------

The easiest situation you can find yourself in is that there's a typo in the source code, and the property access needs to be corrected to the right name:

```diff-php
 	public function getName(): string
 	{
-		return $this->nama;
+		return $this->name;
 	}
```


Declare the property
----------------

In older versions of PHP you weren't required to declare the property, and the code might have actually run fine. But for obvious type safety reasons it's better to declare the property if it's missing:

```diff-php
 class HelloWorld
 {

+	private string $name;
+
 	public function setName(string $name): void
 	{
 		$this->name = $name;
```


PHP 8.2: Add `#[AllowDynamicProperties]`
----------------

If the class is meant to have dynamic properties (when they vary from object to object), it needs to meet one of the following conditions:

* Have `__get` and/or `__set` magic methods for handling dynamic properties
* Be `stdClass` or extend `stdClass`
* Have `#[AllowDynamicProperties]` class attribute

This is because [dynamic properties have been deprecated](https://php.watch/versions/8.2/dynamic-properties-deprecated) in PHP 8.2+.

Addressing these requirements will **not make the PHPStan error go away**, but it will **allow** the following solutions to work if you use PHP 8.2 or later.


Universal object crates
----------------

Classes without predefined structure are common in PHP applications. They are used as universal holders of data - any property can be set and read on them. Notable examples include `stdClass`, `SimpleXMLElement` (these are enabled by default), objects with results of database queries etc. Use [`universalObjectCratesClasses`](/config-reference#universal-object-crates) key to let PHPStan know which classes with these characteristics are used in your codebase by setting them in your [configuration file](/config-reference):

```yaml
parameters:
	universalObjectCratesClasses:
		- Dibi\Row
		- Ratchet\ConnectionInterface
```

See also [object shape](/writing-php-code/phpdoc-types#object-shapes) PHPDoc type for a better alternative that lets you describe types of properties of such objects.


Add `@property` PHPDoc
----------------

If the class features [magic properties](/writing-php-code/phpdocs-basics#magic-properties) but these properties are always the same, you can declare them with `@property` PHPDocs:

```php
/**
 * @property int $foo
 * @property-read string $bar
 * @property-write \stdClass $baz
 */
class Foo { ... }
```


Mixin
----------------

When a class delegates unknown method calls and property accesses to a different class using `__call` and `__get`/`__set`, we can describe the relationship using `@mixin` PHPDoc tag:

```php
class A
{
	public string $name = 'Class A';
}

/**
 * @mixin A
 */
class B
{
	public function __get(string $name): mixed
	{
		return (new A())->$name;
	}
}

$b = new B();
echo $b->name; // No error
```


Extensions for popular frameworks
----------------

If you use a popular framework like Laravel, where model properties are often not declared on class, you can use framework-specific extensions to make PHPStan understand your code properly.

Check out the [extension library](/user-guide/extension-library) and search for [PHPStan extensions on Packagist](https://packagist.org/?type=phpstan-extension).


Write your own extension
----------------

You can write your own [class reflection extension](/developing-extensions/class-reflection-extensions) to let PHPStan know which properties exist on your classes.

This solution works best if there's custom logic in your `__get()` and `__set()` magic methods. For example if you can say "Property `$name` on class `Foo` exists if there's a getter called `getName()`", writing a custom class reflection extension to describe this lets you solve this PHPStan error in a nice and easy way.

---

Do you like PHPStan and use it every day? [**Consider supporting further development of PHPStan on GitHub Sponsors**](https://github.com/sponsors/ondrejmirtes/). I’d really appreciate it!
