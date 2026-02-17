---
title: "assignOp.invalid"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$a = 'hello';
$a -= 5;
```

## Why is it reported?

The compound assignment operator (such as `+=`, `-=`, `*=`, `.=`, etc.) cannot be applied to the given combination of types. The operation between the left-hand variable and the right-hand value produces a type error.

In the example above, the `-=` operator is applied between a `string` and an `int`, which is not a valid binary operation.

## How to fix it

Ensure the variable and the value are compatible types for the operation:

```diff-php
 <?php declare(strict_types = 1);

-$a = 'hello';
-$a -= 5;
+$a = 10;
+$a -= 5;
```

Or use the correct operator for the types involved:

```diff-php
 <?php declare(strict_types = 1);

 $a = 'hello';
-$a -= 5;
+$a .= ' world';
```
