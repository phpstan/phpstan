---
title: "doctrine.enumType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use Doctrine\ORM\Mapping as ORM;

enum Status: string
{
	case Active = 'active';
	case Inactive = 'inactive';
}

#[ORM\Entity]
class User
{
	#[ORM\Column(type: "integer", enumType: Status::class)]
	public Status $status;
}
```

## Why is it reported?

When using Doctrine's `enumType` column mapping, the backing type of the PHP enum must match the database column type. In the example above, the `Status` enum is backed by `string`, but the Doctrine column is mapped as `integer`. This mismatch means Doctrine cannot correctly serialize and deserialize the enum values to and from the database.

## How to fix it

Align the database column type with the enum's backing type:

```diff-php
 <?php declare(strict_types = 1);

 use Doctrine\ORM\Mapping as ORM;

 #[ORM\Entity]
 class User
 {
-	#[ORM\Column(type: "integer", enumType: Status::class)]
+	#[ORM\Column(type: "string", enumType: Status::class)]
 	public Status $status;
 }
```

Or change the enum's backing type to match the database column:

```diff-php
 <?php declare(strict_types = 1);

-enum Status: string
+enum Status: int
 {
-	case Active = 'active';
-	case Inactive = 'inactive';
+	case Active = 1;
+	case Inactive = 0;
 }
```
