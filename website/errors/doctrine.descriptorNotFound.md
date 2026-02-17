---
title: "doctrine.descriptorNotFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class User
{
	#[ORM\Column(type: 'custom_type')]
	private string $name;
}
```

## Why is it reported?

This error is reported by the [phpstan-doctrine](https://github.com/phpstan/phpstan-doctrine) extension.

The Doctrine column mapping uses a type (`custom_type`) that does not have a registered type descriptor. PHPStan cannot validate the type mapping for this column because the type descriptor is missing from the descriptor registry.

## How to fix it

Register the custom Doctrine type in your configuration, or use a built-in Doctrine type:

```diff-php
 <?php declare(strict_types = 1);

 use Doctrine\ORM\Mapping as ORM;

 #[ORM\Entity]
 class User
 {
-	#[ORM\Column(type: 'custom_type')]
+	#[ORM\Column(type: 'string')]
 	private string $name;
 }
```

If using a custom type, make sure it is properly registered with Doctrine's type system and configure the phpstan-doctrine extension to recognise it.
