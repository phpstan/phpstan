---
title: "varTag.internalEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Internal\CacheStrategy;

/** @var CacheStrategy $strategy */
$strategy = getStrategy();
```

## Why is it reported?

The `@var` PHPDoc tag references an enum that is marked as `@internal` in another package. Internal symbols are not meant to be used outside of their own package. Referencing them in `@var` tags creates a dependency on implementation details that can change without notice.

## How to fix it

Use the public API type instead of the internal enum in the `@var` tag.

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use Internal\CacheStrategy;
+use Public\CacheStrategy;

-/** @var CacheStrategy $strategy */
+/** @var CacheStrategy $strategy */
 $strategy = getStrategy();
```
