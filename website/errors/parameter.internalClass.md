---
title: "parameter.internalClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Acme\Library\InternalHelper;

function process(InternalHelper $helper): void
{
}
```

## Why is it reported?

A function or method parameter uses a class marked with the `@internal` tag from another package as its type declaration. Internal classes are implementation details of the library and are not part of its public API. They may change or be removed in future versions without notice. Using an internal class as a parameter type creates a dependency on an unstable API.

## How to fix it

Replace the internal class with a public API type, such as an interface or a non-internal class:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use Acme\Library\InternalHelper;
+use Acme\Library\HelperInterface;

-function process(InternalHelper $helper): void
+function process(HelperInterface $helper): void
 {
 }
```
