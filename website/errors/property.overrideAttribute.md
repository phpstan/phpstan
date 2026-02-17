---
title: "property.overrideAttribute"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Base
{

	public int $value = 0;

}

class Child extends Base
{

	#[\Override]
	public int $value = 1;

}
```

## Why is it reported?

The `#[\Override]` attribute is being used on a property, but this feature is only supported on PHP 8.5 and later. On earlier PHP versions, the `#[\Override]` attribute is only valid on methods, not properties.

## How to fix it

Remove the `#[\Override]` attribute from the property if you are running a PHP version older than 8.5:

```diff-php
 <?php declare(strict_types = 1);

 class Base
 {

 	public int $value = 0;

 }

 class Child extends Base
 {

-	#[\Override]
 	public int $value = 1;

 }
```

Alternatively, upgrade to PHP 8.5 or later where property overrides with `#[\Override]` are supported.
