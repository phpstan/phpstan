---
title: "method.internalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App\Service;

use Acme\Library\SomeClass;

// SomeClass uses an @internal trait
$obj = new SomeClass();
$obj->traitMethod();
```

## Why is it reported?

A method is being called on an object whose declaring class is a trait marked with the `@internal` tag. The trait is an implementation detail of the library and is not part of its public API. Methods originating from internal traits should not be called from outside the library because the trait may change or be removed in future versions.

## How to fix it

Avoid calling methods that come from internal traits. Look for alternative public API methods that provide the same functionality.

```diff-php
 <?php declare(strict_types = 1);

 namespace App\Service;

 use Acme\Library\SomeClass;

 $obj = new SomeClass();
-$obj->traitMethod();
+$obj->publicApiMethod();
```
