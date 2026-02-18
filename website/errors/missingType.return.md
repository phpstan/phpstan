---
title: "missingType.return"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function findUser(int $id)
{
	// ...
}
```

## Why is it reported?

The function or method has no return type specified, either as a native PHP return type or through a `@return` PHPDoc tag. Without a return type, PHPStan treats the return value as `mixed`, which limits its ability to detect type errors at call sites.

## How to fix it

Add a native return type declaration:

```diff-php
-function findUser(int $id)
+function findUser(int $id): ?User
 {
 	// ...
 }
```

If native return types cannot be used (e.g. for backward compatibility), add a `@return` PHPDoc tag:

```diff-php
+/** @return User|null */
 function findUser(int $id)
 {
 	// ...
 }
```

Learn more about return types in [PHPDoc Basics](/writing-php-code/phpdocs-basics) and [PHPDoc Types](/writing-php-code/phpdoc-types).
