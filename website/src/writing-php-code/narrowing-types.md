---
title: Narrowing Types
---

Narrowing a type is usually needed when we have a [union type](/blog/union-types-vs-intersection-types) like `bool|int` and we want to work only with one of the types in a branch. Other use case for narrowing is when we we want to execute code only for a specific subtype, for example when we're interested only in `InvalidArgumentException` but we've got `Exception` in a variable.

PHPStan supports several ways of narrowing types.

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

// $intOrString is true after the assert() call
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
* `is_a`

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

Custom type-checking functions and methods
----------------------

Narrowing types can also be performed by assertion libraries like [webmozart/assert](https://github.com/webmozart/assert) and [beberlei/assert](https://github.com/beberlei/assert). You can install [PHPStan extensions](/user-guide/extension-library) for these libraries so that PHPStan can understand how these custom function calls narrow down types.

Let's consider a custom type-checking function like this:

```php
public function foo(object $object): void
{
    $this->checkType($object);
    $object->doSomething(); // Call to an undefined method object::doSomething().
}

public function checkType(object $object): void
{
    if (!$object instanceof \BarService) {
        throw new WrongObjectTypeException();
    }
}
```

During the analysis of the `foo()` method, PHPStan doesn't understand that the type of `$object` was narrowed to `BarService` because it doesn't descend to called functions and symbols, it just reads their typehints and PHPDocs.

Recommended ways of solving this problem are:

1) Switching to inlined type check - having `instanceof` right in the `foo()` method.
2) Switching to an assertion library like [webmozart/assert](https://github.com/webmozart/assert) or [beberlei/assert](https://github.com/beberlei/assert) and using it directly in the `foo()` method.
3) Writing a custom [type-specifying extension](/developing-extensions/type-specifying-extensions) for the `checkType()` method.
