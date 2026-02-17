---
title: "function.nameCase"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function myFunction(): void
{
}

MyFunction();
```

## Why is it reported?

The function is being called with a different letter casing than its declaration. While PHP function names are case-insensitive and the code will work at runtime, using incorrect casing is considered poor practice. It makes code harder to read and can cause confusion about which function is being called.

In the example above, `myFunction` is declared with a lowercase `m`, but called as `MyFunction` with an uppercase `M`.

## How to fix it

Match the casing of the function call to the function declaration:

```diff-php
 <?php declare(strict_types = 1);

-MyFunction();
+myFunction();
```
