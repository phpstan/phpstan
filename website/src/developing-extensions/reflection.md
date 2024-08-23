---
title: Reflection
---

PHPStan has its own reflection layer for asking details about functions, classes, properties, methods, and constants.

Various reflection objects can be obtained either on `PHPStan\Type\Type` implementations (see [type system](/developing-extensions/type-system#what-can-a-type-tell-us%3F)), or on [`PHPStan\Reflection\ReflectionProvider`](https://apiref.phpstan.org/1.12.x/PHPStan.Reflection.ReflectionProvider.html) object by asking for it in the constructor of your extension:

```php
private ReflectionProvider $reflectionProvider;

public function __construct(
	ReflectionProvider $reflectionProvider
)
{
	$this->reflectionProvider = $reflectionProvider;
}
```

Functions
------------------

There are three relevant methods about functions on `ReflectionProvider`:

* `public function hasFunction(PhpParser\Node\Name $nameNode, ?PHPStan\Analyser\Scope $scope): bool`
* `public function getFunction(PhpParser\Node\Name $nameNode, ?PHPStan\Analyser\Scope $scope): FunctionReflection`
* `public function resolveFunctionName(PhpParser\Node\Name $nameNode, ?PHPStan\Analyser\Scope $scope): ?string`

These methods need [Scope](/developing-extensions/scope) because when calling function `foo()`, PHP first checks whether function `CurrentNamespace\foo` exists, and if it doesn't, it looks for function `foo` in the global namespace.

The `getFunction` method returns [`PHPStan\Reflection\FunctionReflection`](https://apiref.phpstan.org/1.12.x/PHPStan.Reflection.FunctionReflection.html) which can be used to get information about the function.

`FunctionReflection` doesn't directly contain parameters and return types, but they can be accessed through the `getVariants(): ParametersAcceptor[]` method. That's because some built-in PHP functions have multiple variants, like [`strtok`](https://www.php.net/manual/en/function.strtok.php) or [`implode`](https://www.php.net/manual/en/function.implode.php).

If you have the `PhpParser\Node\Expr\FuncCall` expression, you can obtain the right variant by using `ParametersAcceptorSelector`:

```php
$variant = PHPStan\Reflection\ParametersAcceptorSelector::selectFromArgs(
	$scope,
	$funcCall->getArgs(),
	$functionReflection->getVariants()
);
$parameters = $variant->getParameters();
$returnType = $variant->getReturnType();
```

Global constants
------------------

There are three relevant methods about global constants on `ReflectionProvider`:

* `public function hasConstant(PhpParser\Node\Name $nameNode, ?PHPStan\Analyser\Scope $scope): bool`
* `public function getConstant(PhpParser\Node\Name $nameNode, ?PHPStan\Analyser\Scope $scope): GlobalConstantReflection`
* `public function resolveConstantName(PhpParser\Node\Name $nameNode, ?PHPStan\Analyser\Scope $scope): ?string`

These methods need [Scope](/developing-extensions/scope) because when accessing global constant `FOO`, PHP first checks whether constant `CurrentNamespace\FOO` exists, and if it doesn't, it looks for constant `FOO` in the global namespace.

The `getConstant` method returns [`PHPStan\Reflection\GlobalConstantReflection`](https://apiref.phpstan.org/1.12.x/PHPStan.Reflection.GlobalConstantReflection.html) which can be used to get information about the constant like the value [type](/developing-extensions/type-system) with the `getValueType(): Type` method.

Classes
------------------

The [`PHPStan\Reflection\ClassReflection`](https://apiref.phpstan.org/1.12.x/PHPStan.Reflection.ClassReflection.html) can be obtained using the `getClass()` method on `ReflectionProvider`. It's recommended to first check that the class exists using the `hasClass()` method.

`ClassReflection` objects represent not only classes, but also interfaces and traits. You can differentiate between them by asking `isClass()`, `isInterface()`, and `isTrait()`.

`ClassReflection` can be used to query information about extended parent classes, implemented interfaces, and used traits:

* `public function getParentClass(): self|false`
* `public function getInterfaces(): self[]`
* `public function getTraits(): self[]`

If you want to get some information that isn't available on `ClassReflection`, you can get the built-in [`ReflectionClass`](https://www.php.net/manual/en/class.reflectionclass.php) instance [^better-reflection] with the `getNativeReflection(): \ReflectionClass` method.

[^better-reflection]: In some cases you'll get the Adapter implementation instead from [BetterReflection](https://github.com/ondrejmirtes/BetterReflection) because of the hybrid approach to [static reflection](/blog/zero-config-analysis-with-static-reflection). But they're compatible.

Properties
------------------

Property reflections can be obtained by using these methods on `ClassReflection`:

* `public function hasProperty(string $propertyName): bool`
* `public function getProperty(string $propertyName, PHPStan\Analyser\Scope $scope): PropertyReflection`

Class reflection works closely with [class reflection extensions](/developing-extensions/class-reflection-extensions) to also return information about magically implemented properties using `__get()` and `__set()`. If you want to only get information about native properties with no regard to `__get()` and `__set()`, use `ClassReflection::hasNativeProperty()` and `ClassReflection::getNativeProperty()` instead of `ClassReflection::hasProperty()` and `ClassReflection::getProperty()`.

Property reflections can also be obtained using methods on `PHPStan\Type\Type`. See [type system](/developing-extensions/type-system) for more details.

The returned [`PropertyReflection`](https://apiref.phpstan.org/1.12.x/PHPStan.Reflection.PropertyReflection.html) object can be used to ask about property's type, visibility, declaring class, and PHPDoc.

Methods
------------------

Method reflections can be obtained by using these methods on `ClassReflection`:

* `public function hasMethod(string $methodName): bool`
* `public function getMethod(string $methodName, PHPStan\Analyser\Scope $scope): MethodReflection`

Class reflection works closely with [class reflection extensions](/developing-extensions/class-reflection-extensions) to also return information about magically implemented methods using `__call()` and `__callStatic()`. If you want to only get information about native methods with no regard to `__call()` and `__callStatic()`, use `ClassReflection::hasNativeMethod()` and `ClassReflection::getNativeMethod()` instead of `ClassReflection::hasMethod()` and `ClassReflection::getMethod()`.

Method reflections can also be obtained using methods on `PHPStan\Type\Type`. See [type system](/developing-extensions/type-system) for more details.

The returned [`MethodReflection`](https://apiref.phpstan.org/1.12.x/PHPStan.Reflection.MethodReflection.html) object can be used to ask about method's visibility, declaring class, PHPDoc, etc.

`MethodReflection` doesn't directly contain parameters and return types, but they can be accessed through the `getVariants(): ParametersAcceptor[]` method. That's because some built-in PHP methods have multiple variants, like [`PDO::query()`](https://www.php.net/manual/en/pdo.query.php).

If you have the `PhpParser\Node\Expr\MethodCall` expression, you can obtain the right variant by using `ParametersAcceptorSelector`:

```php
$variant = PHPStan\Reflection\ParametersAcceptorSelector::selectFromArgs(
	$scope,
	$methodCall->getArgs(),
	$methodReflection->getVariants()
);
$parameters = $variant->getParameters();
$returnType = $variant->getReturnType();
```

Class constants
------------------

Class constant reflections can be obtained by using these methods on `ClassReflection`:

* `public function hasConstant(string $name): bool`
* `public function getConstant(string $name): ConstantReflection`

Class constant reflections can also be obtained using methods on `PHPStan\Type\Type`. See [type system](/developing-extensions/type-system) for more details.

The returned [`ConstantReflection`](https://apiref.phpstan.org/1.12.x/PHPStan.Reflection.ConstantReflection.html) can be used to ask about constant's visibility, declaring class, PHPDoc, and the value [type](/developing-extensions/type-system) with the `getValueType(): Type` method.

Retrieving custom PHPDocs
------------------

All information that PHPStan itself works with (like parameter types) is already available directly as methods on reflection objects.

If you want to write custom logic using PHPDoc tags that are unknown to PHPStan internals, you can directly access the PHPDoc's abstract syntax tree that's parsed and represented with [phpstan/phpdoc-parser](https://github.com/phpstan/phpdoc-parser) library.

*[AST]: Abstract Syntax Tree

Accessing the PHPDoc is a matter of getting the PHPDoc's string that can look for example like `/** @var Foo */` - reflection objects described in this article have `getDocComment(): ?string` method.

Alternatively, you can obtain the PHPDoc's string from the source code [AST](/developing-extensions/abstract-syntax-tree). All nodes have the `getDocComment(): ?PhpParser\Comment\Doc` method.

Getting the PHPDoc's AST with `PHPStan\Type\FileTypeMapper` by asking for the object through your extension's constructor:

```php
private FileTypeMapper $fileTypeMapper;

public function __construct(FileTypeMapper $fileTypeMapper)
{
	$this->fileTypeMapper = $fileTypeMapper;
}
```

`FileTypeMapper` has `getResolvedPhpDoc` method:

```php
public function getResolvedPhpDoc(
	string $fileName,
	?string $className,
	?string $traitName,
	?string $functionName,
	string $docComment
): ResolvedPhpDocBlock
```

Beside the PHPDoc's string itself, you also need to provide the location of the PHPDoc. The call typically looks like this:

```php
$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
	$scope->getFile(),
	$scope->isInClass() ? $scope->getClassReflection()->getName() : null,
	$scope->isInTrait() ? $scope->getTraitReflection()->getName() : null,
	$function !== null ? $function->getName() : null,
	$docComment
);
```

It returns the `ResolvedPhpDocBlock` object. The [phpstan/phpdoc-parser](https://github.com/phpstan/phpdoc-parser) AST can be obtained by calling `getPhpDocNodes(): PhpDocNode[]` method. The whole PHPDoc is represented by a single root node. So why it returns an array of nodes? Because PHPStan merges PHPDocs from overridden methods to get the complete picture, so all of these nodes are available as a returned value from the `getPhpDocNodes()` method.
