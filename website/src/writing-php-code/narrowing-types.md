---
title: Narrowing Types
---

Narrowing a type is usually needed when we have a [union type](/blog/union-types-vs-intersection-types) like `bool|int` and we want to work only with one of the types in a branch. Other use case for narrowing is when we want to execute code only for a specific subtype, for example when we're interested only in `InvalidArgumentException` but we've got `Exception` in a variable.

PHPStan supports several ways of narrowing types.

PHPDocs and native types
----------------------

The best way is to avoid this issue altogether. By using precise parameter types and asking for only the one type we want to work with, we don't need to do any further type-narrowing in the function body:

```php
function doFoo(Article $a): void
{
	// it only makes sense for this function to accept Articles
	// do not make the parameter type unnecessarily wide enough
}
```

Same applies to return types. Inform the code that calls your function about the precise type it returns:

```php
function doBar(): Article
{
	// write functions that only return a single precise type
	// and not a general "object" or "mixed"

	// ...
	return $article;
}
```

You can also take advantage of [advanced PHPDoc types in `@param` and `@return`](/writing-php-code/phpdoc-types). Also check out [Generics](/blog/generics-by-examples).

Strict comparison operators
----------------------

In case of scalar values type can be narrowed using `===` and `!==` operators:

```php
if ($stringOrBool === true) {
    // $stringOrBool is true here
}
```

These operators can either be used in conditions, or in an `assert()` call:

```php
assert($stringOrBool === true);

// $stringOrBool is true after the assert() call
```

Type-checking functions
----------------------

* `is_array()`
* `is_bool()`
* `is_callable()`
* `is_float()` / `is_double()` / `is_real()`
* `is_int()` / `is_integer()` / `is_long()`
* `is_numeric()`
* `is_iterable()`
* `is_null()`
* `is_object()`
* `is_resource()`
* `is_scalar()`
* `is_string()`
* `is_subclass_of()`
* `is_a()`

These functions can either be used in conditions, or in an `assert()` call.

instanceof operator
----------------------

```php
// $exception is Exception
if ($exception instanceof \InvalidArgumentException) {
    // $exception is \InvalidArgumentException
}
```

The `instanceof` operator can either be used in conditions, or in an `assert()` call.


PHPDoc Types
----------------------

The best way to enforce more specific types throughout your codebase is to [use them in PHPDocs](/writing-php-code/phpdoc-types) and have them checked by PHPStan.

For example: If you call a function from your code that requires a `non-empty-string`:

> `Parameter #1 $contents of function doFoo() expects non-empty-string, string given.`

You can eliminate the empty string `''` before proceeding further with a call like `assert($s !== '')`, but that still allows your function to be called with `''` and crash after the fact.

If you also annotate your function with `@param non-empty-string $s`, this spreads the type throughout your whole codebase, making it overall safer.

Custom type-checking functions and methods
----------------------

Let's consider a custom type-checking function like this:

```php
public function foo(object $object): void
{
    $this->checkType($object);
    $object->doSomething(); // Call to an undefined method object::doSomething().
}

public function checkType(object $object): void
{
    if (!$object instanceof BarService) {
        throw new WrongObjectTypeException();
    }
}
```

During the analysis of the `foo()` method, PHPStan doesn't understand that the type of `$object` was narrowed to `BarService` because it doesn't descend to called functions and symbols, it just reads their typehints and PHPDocs.

PHPStan 1.9.0 and newer supports custom PHPDoc tags `@phpstan-assert`, `@phpstan-assert-if-true`, `@phpstan-assert-if-false` to tell the analyzer about what's going on inside the called function and how types are narrowed. Besides arguments, it also supports narrowing types of properties and returned values from other methods on the same object.

```php
public function foo(object $object): void
{
    $this->checkType($object);
    $object->doSomething(); // No error
    \PHPStan\dumpType($object); // BarService
}

/** @phpstan-assert BarService $object */
public function checkType(object $object): void
{
    if (!$object instanceof BarService) {
        throw new WrongObjectTypeException();
    }
}
```

If the called function doesn't throw an exception, but indicates the type via `bool` return type:

```php
public function foo(mixed $arg): void
{
    if ($this->isStdClass($arg)) {
        \PHPStan\dumpType($arg); // stdClass
    }
}

/** @phpstan-assert-if-true \stdClass $arg */
public function isStdClass(mixed $arg): bool
{
    return $arg instanceof \stdClass;
}
```

These `@phpstan-assert` tags also seamlessly work with [generics](/blog/generics-in-php-using-phpdocs):

```php
/**
 * @template T of object
 * @param class-string<T> $class
 * @phpstan-assert-if-true T $object
 */
public function isObjectOfClass(string $class, object $object): bool
{
    return $object instanceof $class;
}
```

Type negation is also supported:

```php
/**
 * @phpstan-assert !string $arg
 */
public function checkNotString(mixed $arg): void
{
    if (is_string($arg)) {
        throw new \Exception();
    }
}
```

When an object has "hassers" and "getters", PHPStan can be informed about their relation:

```php
/** @phpstan-assert-if-true !null $this->getName() */
public function hasName(): bool
{
    return $this->name !== null;
}

public function getName(): ?string
{
    return $this->name;
}
```

By default `@phpstan-assert-if-true` also makes assuptions about `false` return value, in case this is not desired use `=` operator before the type.
```php
/**
* @phpstan-assert-if-true =Admin $this->admin
* @phpstan-assert-if-true =true $this->admin->active
*/
public function isAdmin(): bool
{
    return $this->admin !== null && $this->admin->active === true;
}

...

if ($user->isAdmin()) {
    // $user->admin is asserted Admin
    // $user->admin->active is asserted true
} else {
    // $user->admin is Admin|null
    // $user->admin->active is false|true
}
```

Type-specifying extensions
----------------------

Instead of writing custom PHPDoc tags, you can also implement a custom [type-specifying extension](/developing-extensions/type-specifying-extensions).

Narrowing types can also be performed by assertion libraries like [webmozart/assert](https://github.com/webmozart/assert) and [beberlei/assert](https://github.com/beberlei/assert). You can install [PHPStan extensions](/user-guide/extension-library) for these libraries so that PHPStan can understand how these custom function calls narrow down types.
