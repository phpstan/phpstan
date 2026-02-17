---
title: "parameter.internalInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Acme\Library\InternalInterface;

function process(InternalInterface $handler): void
{
}
```

## Why is it reported?

A function or method parameter uses an interface marked with the `@internal` tag from another package as its type declaration. Internal interfaces are implementation details of the library and are not part of its public API. They may change or be removed in future versions without notice. Using an internal interface as a parameter type creates a dependency on an unstable API.

## How to fix it

Replace the internal interface with a public API type:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use Acme\Library\InternalInterface;
+use Acme\Library\PublicInterface;

-function process(InternalInterface $handler): void
+function process(PublicInterface $handler): void
 {
 }
```
