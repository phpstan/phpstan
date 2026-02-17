---
title: "trait.nameInUse"
ignorable: false
---

## Code example

```php
<?php

namespace App;

use SomeLibrary\Helper;

trait Helper
{
}
```

## Why is it reported?

A trait is being declared with a name that is already in use within the same namespace, typically because a `use` import statement has already introduced the same name. PHP does not allow two symbols with the same name in the same namespace scope, and this will cause a fatal error at runtime.

## How to fix it

Rename the trait to avoid the name conflict:

```diff-php
 <?php

 namespace App;

 use SomeLibrary\Helper;

-trait Helper
+trait AppHelper
 {
 }
```

Or alias the imported name to avoid the collision:

```diff-php
 <?php

 namespace App;

-use SomeLibrary\Helper;
+use SomeLibrary\Helper as LibraryHelper;

 trait Helper
 {
 }
```
