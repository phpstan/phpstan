---
title: Closure Extensions
---

Parameter Closure Type
---------------

Sometimes, you might want to change the type of a closure parameter to a function or method call based on the context. For example, the closure might have a generic argument, and you want to change the inner type.

You can create an extension that implements [MethodParameterClosureTypeExtension](https://apiref.phpstan.org/2.1.x/PHPStan.Type.MethodParameterClosureTypeExtension.html):

```php
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;

interface MethodParameterClosureTypeExtension
{
 public function isMethodSupported(
  MethodReflection $methodReflection,
  ParameterReflection $parameter,
 ): bool;
 
 public function getTypeTypeFromMethodCall(
  MethodReflection $methodReflection,
  MethodCall $methodCall,
  ParameterReflection $parameter,
  Scope $scope,
 ): ?Type;
}
```

The implementation needs to be registered in your [configuration file](/config-reference):

```yaml
services:
 -
  class: MyApp\PHPStan\SomeParameterClosureTypeExtension
  tags:
   - phpstan.methodParameterClosureTypeExtension
```

There's also analogous functionality for:

* **static methods** using [`StaticMethodParameterClosureTypeExtension`](https://apiref.phpstan.org/2.1.x/PHPStan.Type.StaticMethodParameterClosureTypeExtension.html) interface and `phpstan.staticMethodParameterClosureTypeExtension` service tag.
* **functions** using [`FunctionParameterClosureTypeExtension`](https://apiref.phpstan.org/2.1.x/PHPStan.Type.FunctionParameterClosureTypeExtension.html) interface and `phpstan.functionParameterClosureTypeExtension` service tag.

Parameter Closure This
---------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.1.23</div>

While PHPStan supports `@param-closure-this` to [change the meaning of `$this`](/writing-php-code/phpdocs-basics#callables) inside closures, sometimes static PHPDocs are not sufficient or don't have the necessary context to describe the closure's `$this` parameter.

You can create an extension that implements [MethodParameterClosureThisExtension](https://apiref.phpstan.org/2.1.x/PHPStan.Type.MethodParameterClosureThisExtension.html):

```php
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;

interface MethodParameterClosureThisExtension
{
 public function isMethodSupported(
  MethodReflection $methodReflection,
  ParameterReflection $parameter,
 ): bool;
 
 public function getClosureThisTypeFromMethodCall(
  MethodReflection $methodReflection,
  MethodCall $methodCall,
  ParameterReflection $parameter,
  Scope $scope,
 ): ?Type;
}
```

The implementation needs to be registered in your [configuration file](/config-reference):

```yaml
services:
 -
  class: MyApp\PHPStan\SomeParameterClosureThisExtension
  tags:
   - phpstan.methodParameterClosureThisExtension
```

There's also analogous functionality for:

* **static methods** using [`StaticMethodParameterClosureThisExtension`](https://apiref.phpstan.org/2.1.x/PHPStan.Type.StaticMethodParameterClosureThisExtension.html) interface and `phpstan.staticMethodParameterClosureThisExtension` service tag.
* **functions** using [`FunctionParameterClosureThisExtension`](https://apiref.phpstan.org/2.1.x/PHPStan.Type.FunctionParameterClosureThisExtension.html) interface and `phpstan.functionParameterClosureThisExtension` service tag.
