---
title: "methodTag.internalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Acme\Library\InternalTrait;

/**
 * @method InternalTrait getTrait()
 */
class Service
{
}
```

## Why is it reported?

A `@method` PHPDoc tag references a trait marked with the `@internal` tag from another package. Internal traits are implementation details of the library and are not part of its public API. They may change or be removed in future versions without notice.

## How to fix it

Replace the internal trait type reference with a public API type:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @method InternalTrait getTrait()
+ * @method PublicInterface getTrait()
  */
 class Service
 {
 }
```
