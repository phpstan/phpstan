---
title: Scope
---

The [`PHPStan\Analyser\Scope`](https://apiref.phpstan.org/1.12.x/PHPStan.Analyser.Scope.html) object can be used to get more information about the code, like types of variables, or current file and namespace.

It's passed as a method parameter to these types of extensions:

* [Custom rules](/developing-extensions/rules) (2nd parameter of `processNode` method)
* [Dynamic return type extensions](/developing-extensions/dynamic-return-type-extensions) (last parameter of `getTypeFrom*Call` methods)
* [Dynamic throw type extensions](/developing-extensions/dynamic-throw-type-extensions) (last parameter of `getTypeFrom*Call` methods)
* [Type-specifying extensions](/developing-extensions/type-specifying-extensions) (3rd parameter of `specifyTypes` method)

The Scope represents the state of the analyser in the position in the [AST](/developing-extensions/abstract-syntax-tree) where the extension is being executed.

```php
public function doFoo(?array $list): void
{
	// Scope knows we're in doFoo() method here
	// And it knows that $list is either an array or null
	if ($list !== null) {
		// Scope knows that $list is an array
		$foo = true;
		// Scope knows that $foo is true
	} else {
		// Scope knows that $list is null
		$foo = false;
		// Scope knows that $foo is false
	}

	// Scope knows that $list is either an array or null
	// Scope knows that $foo is bool
}
```

The most interesting method is `Scope::getType(PhpParser\Node\Expr $expr): PHPStan\Type\Type`. It allows us to ask for types of expressions like this:

```php
// $methodCall is PhpParser\Node\Expr\MethodCall
// On which type was the method called?
$calledOnType = $scope->getType($methodCall->var);
```

The `getType()` method returns an implementation of `PHPStan\Type\Type`. You can learn more about types in the article about [the type system](/developing-extensions/type-system).

Where are we?
--------------

You can use different `Scope` methods to ask about the current whereabouts:

* `getFile()` - returns the current file path
* `getNamespace()` - returns the current namespace
* `isInClass()` - tells the caller whether we are in a class
* `getClassReflection()` - returns the [reflection](/developing-extensions/reflection) of the current class. Cannot return `null` if `isInClass()` is true.
* `isInTrait()` - tells the caller whether we are in a trait
* `getTraitReflection()` - returns the [reflection](/developing-extensions/reflection) of the current trait. Cannot return `null` if `isInTrait()` is true.
* `getFunction()` - returns `FunctionReflection`, `MethodReflection`, or null.
* `isInAnonymousFunction()` - tells the caller whether we are in an anonymous function
* `getAnonymousFunctionReflection()` - returns anonymous function [reflection](/developing-extensions/reflection), or null.

Resolving special names
--------------

In the case of relative names, `PhpParser\Node\Name` AST node instances are resolved according to the current namespace and `use` statements, however certain special names like `self` remain unresolved.

For example we can have `PhpParser\Node\Expr\StaticCall` that represents static method call like `self::doFoo()`. If we were to use the value in its `$class` property as a definitive class name, we'd work with `self`, but there's no `class self` defined in our code. We first need to resolve `self` to the correct name:

```php
// $staticCall is PhpParser\Node\Expr\StaticCall
$className = $scope->resolveName($staticCall->class);
```
