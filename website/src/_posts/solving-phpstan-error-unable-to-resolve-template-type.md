---
title: 'Solving PHPStan error "Unable to resolve the template type"'
date: 2021-01-13
tags: guides
---

This problem is reported by PHPStan on [level 6](/user-guide/rule-levels) when calling a [generic](/blog/generics-in-php-using-phpdocs) method or function. Generic methods and functions declare so-called template types (also known as type variables) via `@template` PHPDoc tags.

The return type of a generic function depends on input arguments. Let's take this example:

```php
/**
 * @template T
 * @param T[] $arg
 * @return T|null
 */
function firstOrNull(array $arg)
{
    // ...
}
```

The function takes an input array and will return the first element in case the array is not empty.

PHPStan needs to resolve what `T` is when calling the function. If it's not able to do it, it will report "Unable to resolve the template type T in call to function firstOrNull".

For example, when `mixed` is passed to the function, it's not considered an argument type error, but because a generic function is called, the expectation is that `T` will be something concrete. To prevent `mixed` to be propagated further down the line, PHPStan reports this error and wants the user to pass a more specific argument to the function. So instead of `mixed`, it wants an array of elements so that it can extract `T` from it, like an array of integers `int[]` will resolve `T` to be `int`.
