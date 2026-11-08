---
title: "argument.named"
shortDescription: "Named argument used in a call that does not accept named arguments."
ignorable: true
autofixable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @no-named-arguments */
function createTag(string $name, string ...$classes): string
{
	return '';
}

createTag(name: 'div', classes: 'bold');
```

## Why is it reported?

A named argument or an array with string keys is being unpacked in a call to a function or method that does not accept named arguments. The function is annotated with `@no-named-arguments`, which means callers must only use positional arguments.

Named arguments may also be disallowed for internal PHP functions that do not support them.

## How to fix it

Use positional arguments instead of named ones:

```diff-php
 <?php declare(strict_types = 1);

 /** @no-named-arguments */
 function createTag(string $name, string ...$classes): string
 {
 	return '';
 }

-createTag(name: 'div', classes: 'bold');
+createTag('div', 'bold');
```

Running `vendor/bin/phpstan analyse --fix` rewrites the call to pass all arguments positionally. Named arguments are put in the order of the parameters, and optional parameters skipped in between are filled with their default values:

```diff-php
 /** @no-named-arguments */
 function foo(int $a, int $b = 2, string $c = 'c'): void
 {
 }

-foo(c: 'x', a: 1);
+foo(1, 2, 'x');
```

The call is left for you to rewrite by hand when it cannot be expressed positionally without changing its meaning — for example when it also unpacks an array, when a named argument would be collected by a variadic parameter (like `classes:` in the example above), or when a skipped parameter's default value cannot be written as code. Named arguments in attributes are not fixed either, and neither are named arguments passed to a callback through `call_user_func()` or `call_user_func_array()`.

If unpacking an array with string keys, convert it to a list first:

```diff-php
-createTag(...$arrayWithStringKeys);
+createTag(...array_values($arrayWithStringKeys));
```
