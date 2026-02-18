---
title: "attribute.deprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewInterface instead */
interface OldInterface {}

#[OldInterface]
class Foo {}
```

## Why is it reported?

An attribute references an interface that has been marked as `@deprecated`. Using deprecated interfaces in attributes should be avoided because they are scheduled for removal in a future version.

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

## How to fix it

Use the replacement suggested in the deprecation message:

```diff-php
 <?php declare(strict_types = 1);

-#[OldInterface]
+#[NewInterface]
 class Foo {}
```
