---
title: "trait.duplicateEnumCase"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Status
{
	case Active;
	case Active;
}
```

## Why is it reported?

An enum case with the same name is declared more than once in the same type. In the example above, the `Active` case is declared twice in the `Status` enum. PHP does not allow duplicate enum case names.

## How to fix it

Remove the duplicate enum case:

```diff-php
 <?php declare(strict_types = 1);

 enum Status
 {
 	case Active;
-	case Active;
 }
```
