---
title: "property.inInterface"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface HasName
{
	public string $name; // ERROR: Interfaces can include properties only on PHP 8.4 and later.
}
```

## Why is it reported?

Before PHP 8.4, interfaces cannot declare properties. Property declarations in interfaces are a PHP 8.4 feature that works together with property hooks, allowing interfaces to define abstract hooked properties that implementing classes must provide.

This error is not ignorable because it represents a PHP language-level constraint.

## How to fix it

If running PHP 8.4 or later, declare the property as a hooked property in the interface:

```diff-php
 <?php declare(strict_types = 1);

 interface HasName
 {
-	public string $name;
+	public string $name { get; set; }
 }
```

On earlier PHP versions, use getter and setter methods instead:

```diff-php
 <?php declare(strict_types = 1);

 interface HasName
 {
-	public string $name;
+	public function getName(): string;
+	public function setName(string $name): void;
 }
```
