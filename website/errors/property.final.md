---
title: "property.final"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class User
{
	final public string $name = 'John';
}
```

## Why is it reported?

The `final` modifier on properties is a feature introduced in PHP 8.4. When running on an earlier PHP version, this syntax is not supported and will cause an error.

## How to fix it

Upgrade to PHP 8.4 or later to use final properties.

If you cannot upgrade, remove the `final` modifier from the property:

```diff-php
 <?php declare(strict_types = 1);

 class User
 {
-	final public string $name = 'John';
+	public string $name = 'John';
 }
```
