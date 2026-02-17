---
title: "property.staticAsymmetricVisibility"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Counter
{

	public protected(set) static int $count = 0;

}
```

## Why is it reported?

Asymmetric visibility (such as `protected(set)` or `private(set)`) is being used on a static property, but this combination is only supported on PHP 8.5 and later. On earlier PHP versions, static properties cannot have different read and write visibility levels.

## How to fix it

Remove the asymmetric visibility from the static property:

```diff-php
 <?php declare(strict_types = 1);

 class Counter
 {

-	public protected(set) static int $count = 0;
+	protected static int $count = 0;

 }
```

Alternatively, upgrade to PHP 8.5 or later where asymmetric visibility for static properties is supported.
