---
title: "magicConstant.outOfNamespace"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

echo __NAMESPACE__;
```

## Why is it reported?

The magic constant `__NAMESPACE__` is used outside of any namespace declaration, where its value is always an empty string. This usually indicates a logic error, as the code likely expects the constant to contain a meaningful namespace value.

## How to fix it

Place the code inside a namespace declaration:

```diff-php
-<?php declare(strict_types = 1);
+<?php declare(strict_types = 1);
+
+namespace App;

 echo __NAMESPACE__;
```

Or use the expected namespace string directly if the namespace is known.
