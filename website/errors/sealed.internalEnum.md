---
title: "sealed.internalEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Internal\Status;

/**
 * @phpstan-sealed Status
 */
interface StatusProvider
{
}
```

## Why is it reported?

The `@phpstan-sealed` PHPDoc tag references an enum that is marked as `@internal` in another package. Internal symbols are not meant to be used outside of their own package, and referencing them in a `@phpstan-sealed` tag creates a dependency on an implementation detail that could change without notice.

## How to fix it

Reference only public (non-internal) enums in `@phpstan-sealed` tags, or create your own enum that mirrors the needed values.

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use Internal\Status;
+use App\Enums\Status;

 /**
  * @phpstan-sealed Status
  */
 interface StatusProvider
 {
 }
```
