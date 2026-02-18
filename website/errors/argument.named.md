---
title: "argument.named"
ignorable: true
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

If unpacking an array with string keys, convert it to a list first:

```diff-php
-createTag(...$arrayWithStringKeys);
+createTag(...array_values($arrayWithStringKeys));
```
