---
title: Restricted Usage Extensions
---

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.1.13</div>

These extensions allow you to restrict where methods, properties, functions etc. can be accessed from without implementing full-fledged [custom rules](/developing-extensions/rules). The implementation is all about applying the [core concepts](/developing-extensions/core-concepts) so check out that guide first and then continue here.

Methods
-----------------

To report method calls from restricted places, implement [`RestrictedMethodUsageExtension`](https://apiref.phpstan.org/2.1.x/PHPStan.Rules.RestrictedUsage.RestrictedMethodUsageExtension.html) interface.

It accepts [method reflection](/developing-extensions/reflection) and [Scope](https://phpstan.org/developing-extensions/scope). It looks like this:

```php
namespace PHPStan\Rules\RestrictedUsage;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ExtendedMethodReflection;

interface RestrictedMethodUsageExtension
{
	public function isRestrictedMethodUsage(
		ExtendedMethodReflection $methodReflection,
		Scope $scope,
	): ?RestrictedUsage;
}
```

This extension is called for traditional method calls like `$foo->doBar(1, 2, 3)`, but also for [first-class callables](https://wiki.php.net/rfc/first_class_callable_syntax) like `$foo->doBar(...)`. It's also called for static method calls like `Foo::doBar(1, 2, 3)` and the first-class callable counterpart `Foo::doBar(...)`.

It can decide not to report anything, or to return [`RestrictedUsage`](https://apiref.phpstan.org/2.1.x/PHPStan.Rules.RestrictedUsage.RestrictedUsage.html) object which carries the error message and identifier.

Here's an example of an implementation:

```php
if (!str_contains($methodReflection->getName(), 'thankYou')) {
	// we're only interested in methods containing "thankYou"
	return null;
}

$inFunction = $scope->getFunction();
if ($inFunction !== null && str_contains($inFunction->getName(), 'please')) {
	// if it's called from a function/method containing "please", it's okay
	return null;
}

return RestrictedUsage::create(
	errorMessage: sprintf(
		'Method %s::%s() is called from %s which does not say "please".',
		$methodReflection->getDeclaringClass()->getDisplayName(),
		$methodReflection->getName(),
		$inFunction !== null ? $inFunction->getName() : 'outside a function',
	),
	identifier: 'method.noPlease',
);
```

Extension implementing RestrictedMethodUsageExtension has to be registered in the [configuration file](/config-reference):

```yaml
	-
		class: App\PHPStan\MyExtension
		tags:
			- phpstan.restrictedMethodUsageExtension
```


Class names
-----------------

The [RestrictedClassNameUsageExtension](https://apiref.phpstan.org/2.1.x/PHPStan.Rules.RestrictedUsage.RestrictedClassNameUsageExtension.html) is called for standalone class name references like in "class extends", "class implements", various parameter and return types, [PHPDoc references](/writing-php-code/phpdocs-basics), but also for static method calls like `Foo::doBar()`, static property and class constants accesses. In total there are [more than 25 locations](https://github.com/phpstan/phpstan-src/blob/2.1.x/src/Rules/ClassNameUsageLocation.php#L18-L43) this extension is called for.

To ease the writing of error messages and identifiers that would differentiate between these locations, this extension is called with an extra argument when compared to other Restricted Usage extensions.

```php
namespace PHPStan\Rules\RestrictedUsage;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\ClassNameUsageLocation;

interface RestrictedClassNameUsageExtension
{
	public function isRestrictedClassNameUsage(
		ClassReflection $classReflection,
		Scope $scope,
		ClassNameUsageLocation $location,
	): ?RestrictedUsage;
}
```

[ClassNameUsageLocation](https://apiref.phpstan.org/2.1.x/PHPStan.Rules.ClassNameUsageLocation.html) object offers `createMessage()` and `createIdentifier()` methods so when the extension decides to return the [`RestrictedUsage`](https://apiref.phpstan.org/2.1.x/PHPStan.Rules.RestrictedUsage.RestrictedUsage.html) object, the creation typically looks like this:

```php
return RestrictedUsage::create(
	$location->createMessage(sprintf(
		'internal %s %s',
		strtolower($classReflection->getClassTypeDescription()), $classReflection->getDisplayName())
	),
	$location->createIdentifier(sprintf('internal%s', $classReflection->getClassTypeDescription())),
);
```

The passed message part in this case is `internal class Foo` and the identifier part `internalClass`. With these values ClassNameUsageLocation will create these message+identifier pairs:

* PHPDoc tag `@property` references internal class Foo. / `propertyTag.internalClass`
* Instantiation of internal class Foo. / `new.internalClass`
* Class Bar implements internal class Foo. / `class.implementsInternalClass`

Extension implementing RestrictedMethodUsageExtension has to be registered in the [configuration file](/config-reference):

```yaml
	-
		class: App\PHPStan\MyExtension
		tags:
			- phpstan.restrictedClassNameUsageExtension
```


Properties
-----------------

The remaining extensions are all analogous to the one about methods so if you want to know more details, check out the documentation above.

[`RestrictedPropertyUsageExtension`](https://apiref.phpstan.org/2.1.x/PHPStan.Rules.RestrictedUsage.RestrictedPropertyUsageExtension.html) interface looks like this:

```php
namespace PHPStan\Rules\RestrictedUsage;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ExtendedPropertyReflection;

interface RestrictedPropertyUsageExtension
{
	public function isRestrictedPropertyUsage(
		ExtendedPropertyReflection $propertyReflection,
		Scope $scope,
	): ?RestrictedUsage;
}
```

This extension is called for both instance property accesses like `$foo->bar` but also for static property accesses like `Foo::$bar`.

Extension implementing RestrictedPropertyUsageExtension has to be registered in the [configuration file](/config-reference):

```yaml
	-
		class: App\PHPStan\MyExtension
		tags:
			- phpstan.restrictedPropertyUsageExtension
```


Class constants
-----------------

[`RestrictedClassConstantUsageExtension`](https://apiref.phpstan.org/2.1.x/PHPStan.Rules.RestrictedUsage.RestrictedClassConstantUsageExtension.html) interface looks like this:

```php
namespace PHPStan\Rules\RestrictedUsage;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassConstantReflection;

interface RestrictedClassConstantUsageExtension
{
	public function isRestrictedClassConstantUsage(
		ClassConstantReflection $constantReflection,
		Scope $scope,
	): ?RestrictedUsage;
}
```

Extension implementing RestrictedClassConstantUsageExtension has to be registered in the [configuration file](/config-reference):

```yaml
	-
		class: App\PHPStan\MyExtension
		tags:
			- phpstan.restrictedClassConstantUsageExtension
```


Functions
-----------------

[`RestrictedFunctionUsageExtension`](https://apiref.phpstan.org/2.1.x/PHPStan.Rules.RestrictedUsage.RestrictedFunctionUsageExtension.html) interface looks like this:

```php
namespace PHPStan\Rules\RestrictedUsage;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;

interface RestrictedFunctionUsageExtension
{
	public function isRestrictedFunctionUsage(
		FunctionReflection $functionReflection,
		Scope $scope,
	): ?RestrictedUsage;
}
```

This extension is called for traditional function calls like `doBar(1, 2, 3)`, but also for [first-class callables](https://wiki.php.net/rfc/first_class_callable_syntax) like `doBar(...)`.

Extension implementing RestrictedFunctionUsageExtension has to be registered in the [configuration file](/config-reference):

```yaml
	-
		class: App\PHPStan\MyExtension
		tags:
			- phpstan.restrictedFunctionUsageExtension
```
