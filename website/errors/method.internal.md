---
title: "method.internal"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App\Service;

use Acme\Library\InternalHelper;

$helper = new InternalHelper();
$helper->processInternal(); // Method is @internal
```

## Why is it reported?

A method marked with the `@internal` PHPDoc tag is being called from outside its allowed scope. Internal methods are implementation details that are not part of the public API. They may change or be removed without notice in future versions, and calling them from external code creates fragile dependencies.

## How to fix it

Use the public API of the class instead of calling its internal methods. Check the class documentation for the intended way to achieve the same result.

```diff-php
 <?php declare(strict_types = 1);

 namespace App\Service;

 use Acme\Library\InternalHelper;

 $helper = new InternalHelper();
-$helper->processInternal();
+$helper->process();
```
