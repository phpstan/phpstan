---
title: "property.assignByRef"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class User
{

	public private(set) string $name = 'John';

}

$user = new User();
$ref = &$user->name;
```

## Why is it reported?

A property with restricted write visibility (such as `private(set)` or `protected(set)`, introduced in PHP 8.4 asymmetric visibility) is being assigned by reference. Assigning by reference creates an alias to the property, which would allow modifying the property value through the reference from outside the allowed scope, bypassing the visibility restriction.

## How to fix it

Assign the property value directly instead of by reference:

```diff-php
 <?php declare(strict_types = 1);

 class User
 {

 	public private(set) string $name = 'John';

 }

 $user = new User();
-$ref = &$user->name;
+$value = $user->name;
```
