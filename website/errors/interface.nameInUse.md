---
title: "interface.nameInUse"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Some\Other\Logger;

interface Logger
{
}
```

## Why is it reported?

The interface declaration uses a name that is already taken by a `use` import or another class-like declaration in the same namespace. PHP does not allow two class-like symbols (classes, interfaces, traits, enums) to share the same name in the same scope. This results in a fatal error at runtime.

## How to fix it

Rename the interface to avoid the conflict:

```diff-php
 namespace App;

 use Some\Other\Logger;

-interface Logger
+interface AppLogger
 {
 }
```

Or remove the conflicting `use` import if it is no longer needed:

```diff-php
 namespace App;

-use Some\Other\Logger;

 interface Logger
 {
 }
```
