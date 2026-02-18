---
title: "cast.void"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

$a = (void)(1 + 1);
```

## Why is it reported?

The `(void)` cast (PHP 8.5+) can only be used as a standalone statement to explicitly discard a return value. It cannot be used within an expression because `(void)` does not produce a value -- assigning it, passing it as an argument, or nesting it inside another expression is not valid.

## How to fix it

Use the `(void)` cast as a standalone statement instead of within an expression:

```diff-php
 <?php declare(strict_types = 1);

-$a = (void)(1 + 1);
+(void)(1 + 1);
```
