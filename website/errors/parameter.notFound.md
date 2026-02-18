---
title: "parameter.notFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @param string $name
 * @param int $age
 * @param string $email
 */
function createUser(string $name, int $age): void
{
}
```

## Why is it reported?

A PHPDoc tag (`@param`, `@param-out`, `@param-closure-this`, `@phpstan-assert`, or a conditional return type) references a parameter name that does not exist in the function or method signature.

In the example above, the `@param string $email` tag references parameter `$email`, which does not exist in the function's parameter list.

This typically happens after renaming or removing a parameter without updating the PHPDoc, or from a typo in the parameter name.

## How to fix it

Remove PHPDoc tags for parameters that no longer exist:

```diff-php
 /**
  * @param string $name
  * @param int $age
- * @param string $email
  */
 function createUser(string $name, int $age): void
 {
 }
```

Or fix the parameter name in the PHPDoc to match the actual parameter:

```diff-php
 /**
  * @param string $name
  * @param int $age
- * @param string $email
+ * @param string $address
  */
-function createUser(string $name, int $age): void
+function createUser(string $name, int $age, string $address): void
 {
 }
```
