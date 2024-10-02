---
title: Class Reflection Extensions
---

Classes in PHP can expose "magic" properties and methods decided in run-time using class methods like `__get`, `__set`, and `__call`. Because PHPStan is all about static analysis (testing code for errors without running it), it has to know about those properties and methods beforehand.

When PHPStan stumbles upon a property or a method that is unknown to built-in class reflection, it iterates over all registered class reflection extensions until it finds one that defines the property or method.

The implementation is all about applying the [core concepts](/developing-extensions/core-concepts) so check out that guide first and then continue here.

Properties class reflection extensions
---------------------

To describe magic properties from `__get` and `__set` methods, an extension must implement the following interface:

```php
namespace PHPStan\Reflection;

interface PropertiesClassReflectionExtension
{

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool;

	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection;

}
```

Most likely you will also have to create a new class implementing the [`PropertyReflection`](https://apiref.phpstan.org/2.0.x/PHPStan.Reflection.PropertyReflection.html) interface:

```php
namespace PHPStan\Reflection;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

interface PropertyReflection
{

	public function getDeclaringClass(): ClassReflection;

	public function isStatic(): bool;

	public function isPrivate(): bool;

	public function isPublic(): bool;

	public function getDocComment(): ?string;

	public function getReadableType(): Type;

	public function getWritableType(): Type;

	public function canChangeTypeAfterAssignment(): bool;

	public function isReadable(): bool;

	public function isWritable(): bool;

	public function isDeprecated(): TrinaryLogic;

	public function getDeprecatedDescription(): ?string;

	public function isInternal(): TrinaryLogic;

}
```

This is how you register the extension in the [configuration file](/config-reference):

```yaml
services:
	-
		class: App\PHPStan\PropertiesFromAnnotationsClassReflectionExtension
		tags:
			- phpstan.broker.propertiesClassReflectionExtension
```

Methods class reflection extensions
---------------------

To describe magic methods from the `__call` method, an extension must implement the [`MethodsClassReflectionExtension`](https://apiref.phpstan.org/2.0.x/PHPStan.Reflection.MethodsClassReflectionExtension.html) interface:

```php
namespace PHPStan\Reflection;

interface MethodsClassReflectionExtension
{

	public function hasMethod(ClassReflection $classReflection, string $methodName): bool;

	public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection;

}
```

Most likely you will also have to implement a new class implementing the [`MethodReflection`](https://apiref.phpstan.org/2.0.x/PHPStan.Reflection.MethodReflection.html) interface:

```php
namespace PHPStan\Reflection;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

interface MethodReflection
{

	public function getDeclaringClass(): ClassReflection;

	public function isStatic(): bool;

	public function isPrivate(): bool;

	public function isPublic(): bool;

	public function getDocComment(): ?string;

	public function getName(): string;

	public function getPrototype(): ClassMemberReflection;

	/**
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getVariants(): array;

	public function isDeprecated(): TrinaryLogic;

	public function getDeprecatedDescription(): ?string;

	public function isFinal(): TrinaryLogic;

	public function isInternal(): TrinaryLogic;

	public function getThrowType(): ?Type;

	public function hasSideEffects(): TrinaryLogic;

}
```

This is how you register the extension in the [configuration file](/config-reference):

```yaml
services:
	-
		class: App\PHPStan\EnumMethodsClassReflectionExtension
		tags:
			- phpstan.broker.methodsClassReflectionExtension
```
