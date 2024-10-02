---
title: Allowed Subtypes
---

PHP language doesn't have a concept of sealed classes - a way to restrict class hierarchies and provide more control over inheritance. So any interface or non-final class can have an infinite number of child classes. But PHPStan provides an extension type to tell the analyzer the complete list of allowed child classes.

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.9.0</div>

The implementation is all about applying the [core concepts](/developing-extensions/core-concepts) like [reflection](/developing-extensions/reflection) so check out that guide first and then continue here.

This is [the interface](https://apiref.phpstan.org/2.0.x/PHPStan.Reflection.AllowedSubTypesClassReflectionExtension.html) your extension needs to implement:

```php
namespace PHPStan\Reflection;

use PHPStan\Type\Type;

interface AllowedSubTypesClassReflectionExtension
{

	public function supports(ClassReflection $classReflection): bool;

	/** @return array<Type> */
	public function getAllowedSubTypes(ClassReflection $classReflection): array;

}
```

The implementation needs to be registered in your [configuration file](/config-reference):

```yaml
services:
	-
		class: MyApp\PHPStan\MySubtypesExtension
		tags:
			- phpstan.broker.allowedSubTypesClassReflectionExtension
```

When you implement this extension, it has a couple of effects:

* Smarter type inference when subtracting types from each other
* Error reporting when a disallowed class implements the restricted interface/extends a restricted parent class

An example
----------------

Let's say you have a class `Foo` and you want only `Bar` and `Baz` to be its child classes:

```php
namespace MyApp\PHPStan;

use PHPStan\Reflection\AllowedSubTypesClassReflectionExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;

class MySubtypesExtension implements AllowedSubTypesClassReflectionExtension
{
	public function supports(ClassReflection $classReflection): bool
	{
		return $classReflection->getName() === Foo::class;
	}

	public function getAllowedSubTypes(ClassReflection $classReflection): array
	{
		return [
			new ObjectType(Bar::class),
			new ObjectType(Baz::class),
		];
	}
}
```

With this extension in place, let's consider this code:

```php
function foo(Foo $foo): void
{
    if ($foo instanceof Bar) {
        return;
    }

    // without the extension, $foo could be "any Foo, but not Bar"
    // with the extension, the only remaining possible type is Baz
    \PHPStan\dumpType($foo); // Baz
}
```

And if you try to extend Foo by a disallowed class:

```php
// Error: 'Type Lorem is not allowed to be a subtype of Foo.'
class Lorem extends Foo
{

}
```

This extension type can be used simply to hardcode a set of classes. But it can also be used [to read custom PHPDocs](/developing-extensions/reflection#retrieving-custom-phpdocs) or class attributes.
