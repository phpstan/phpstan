---
title: Exception Type Resolver
---

PHPStan can enforce [checked and unchecked exceptions](/blog/bring-your-exceptions-under-control). By default, which exceptions are checked is decided by the [`DefaultExceptionTypeResolver`](https://apiref.phpstan.org/__BRANCH__/PHPStan.Rules.Exceptions.DefaultExceptionTypeResolver.html) based on configuration parameters like `exceptions.uncheckedExceptionClasses`.

If you need custom logic to decide whether an exception is checked or unchecked — for example, based on the scope where the exception is thrown — you can implement a custom exception type resolver.

This is [the interface](https://apiref.phpstan.org/__BRANCH__/PHPStan.Rules.Exceptions.ExceptionTypeResolver.html) your extension needs to implement:

```php
namespace PHPStan\Rules\Exceptions;

use PHPStan\Analyser\Scope;

interface ExceptionTypeResolver
{

	public function isCheckedException(string $className, Scope $scope): bool;

}
```

The `isCheckedException()` method receives the exception class name and the current scope. Because the interface accepts a `Scope`, you can make decisions based on the file, namespace, or class where the exception is being thrown.

You can delegate to `DefaultExceptionTypeResolver` for the cases you don't care about by injecting it into your constructor:

```php
namespace App\PHPStan;

use PHPStan\Analyser\Scope;
use PHPStan\Rules\Exceptions\DefaultExceptionTypeResolver;
use PHPStan\Rules\Exceptions\ExceptionTypeResolver;

class MyExceptionTypeResolver implements ExceptionTypeResolver
{

	public function __construct(
		private DefaultExceptionTypeResolver $defaultResolver,
	)
	{
	}

	public function isCheckedException(string $className, Scope $scope): bool
	{
		// In tests, all exceptions are unchecked
		if ($scope->isInClass()
			&& $scope->getClassReflection()->is(\PHPUnit\Framework\TestCase::class)
		) {
			return false;
		}

		// Delegate to default resolver for everything else
		return $this->defaultResolver->isCheckedException($className, $scope);
	}

}
```

With this extension and `exceptions.check.missingCheckedExceptionInThrows` enabled, test methods can throw any exception without declaring `@throws`:

```php
class MyTest extends \PHPUnit\Framework\TestCase
{
	public function testSomething(): void
	{
		// No error - all exceptions are unchecked in tests
		throw new \App\CheckedException();
	}
}

class MyService
{
	// Error: Missing @throws App\CheckedException
	public function doSomething(): void
	{
		throw new \App\CheckedException();
	}
}
```

<div class="bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-4 mb-4" role="alert">

Unlike other extensions, exception type resolver uses a **service override** instead of a service tag. There can only be **one** `ExceptionTypeResolver` per project.

</div>

The implementation needs to be registered as a **service override** in your [configuration file](/config-reference):

```yaml
services:
	exceptionTypeResolver!:
		class: App\PHPStan\MyExceptionTypeResolver
```

Note the `!` after `exceptionTypeResolver` — this overrides the default service with the same name.

[Learn more about checked exceptions »](/blog/bring-your-exceptions-under-control)
