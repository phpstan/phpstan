---
title: "argument.missing"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function createUser(string $name, string $email): void
{
}

createUser(name: 'John');
```

## Why is it reported?

A required parameter was not provided in the function or method call. When using named arguments, all required parameters that are not passed by name must still be supplied. PHP will throw a fatal error at runtime if a required parameter is missing.

In the example above, the `createUser` function requires both `$name` and `$email`, but only the `name` argument is passed.

## How to fix it

Pass all required arguments:

```diff-php
 <?php declare(strict_types = 1);

 function createUser(string $name, string $email): void
 {
 }

-createUser(name: 'John');
+createUser(name: 'John', email: 'john@example.com');
```
