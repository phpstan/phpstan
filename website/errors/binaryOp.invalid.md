---
title: "binaryOp.invalid"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$a = 'hello';
$b = new \stdClass();

$result = $a - $b;
```

## Why is it reported?

A binary operator (such as `+`, `-`, `*`, `/`, `.`, etc.) is used with operands whose types are not compatible for that operation. The operation would result in a `TypeError` at runtime.

In the example above, the `-` operator is applied between a `string` and an `stdClass` object, which is not a valid arithmetic operation.

## How to fix it

Ensure both operands have types compatible with the operator:

```diff-php
 <?php declare(strict_types = 1);

-$a = 'hello';
-$b = new \stdClass();
+$a = 10;
+$b = 5;

 $result = $a - $b;
```

Or use the correct operator for the types involved:

```diff-php
 <?php declare(strict_types = 1);

 $a = 'hello';
-$b = new \stdClass();
+$b = ' world';

-$result = $a - $b;
+$result = $a . $b;
```
