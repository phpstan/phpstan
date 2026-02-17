---
title: "enum.nameInUse"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use App\Enums\Status;

enum Status: string
{
	case Active = 'active';
	case Inactive = 'inactive';
}
```

## Why is it reported?

An enum is being declared with a name that is already in use within the same namespace. This happens when a `use` statement imports a type with the same short name as the enum being declared. PHP cannot resolve which `Status` is intended, resulting in a fatal error at runtime.

In the example above, `Status` is imported via `use App\Enums\Status` and then an enum named `Status` is declared in the `App` namespace, creating a name conflict.

## How to fix it

Rename the enum to avoid the conflict:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

 use App\Enums\Status;

-enum Status: string
+enum UserStatus: string
 {
 	case Active = 'active';
 	case Inactive = 'inactive';
 }
```

Or use an alias for the imported type:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use App\Enums\Status;
+use App\Enums\Status as BaseStatus;

 enum Status: string
 {
 	case Active = 'active';
 	case Inactive = 'inactive';
 }
```

Or remove the `use` statement if the imported type is not needed:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use App\Enums\Status;
-
 enum Status: string
 {
 	case Active = 'active';
 	case Inactive = 'inactive';
 }
```
