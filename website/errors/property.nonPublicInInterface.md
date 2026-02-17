---
title: "property.nonPublicInInterface"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface HasName
{
	protected string $name { get; set; }
}
```

## Why is it reported?

PHP 8.4 introduced hooked properties in interfaces, but all interface properties must be `public`. Interfaces define a public contract that implementing classes must fulfil, so `private` or `protected` visibility is not allowed on interface properties.

## How to fix it

Change the property visibility to `public`:

```diff-php
 <?php declare(strict_types = 1);

 interface HasName
 {
-	protected string $name { get; set; }
+	public string $name { get; set; }
 }
```
