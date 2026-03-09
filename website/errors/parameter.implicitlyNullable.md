---
title: "parameter.implicitlyNullable"
shortDescription: "Parameter has a null default value but its type is not explicitly nullable."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(string $value = null): void
{
}
```

## Why is it reported?

The parameter has a type declaration that does not include `null`, but its default value is `null`. This pattern was used in older PHP code to make a parameter implicitly nullable. Starting with PHP 8.4, this implicit nullable pattern is deprecated.

## How to fix it

Make the nullable type explicit:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(string $value = null): void
+function doFoo(?string $value = null): void
 {
 }
```
