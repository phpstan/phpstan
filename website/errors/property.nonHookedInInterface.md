---
title: "property.nonHookedInInterface"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface HasName
{
	public string $name;
}
```

## Why is it reported?

The interface declares a property without any hooks. Since PHP 8.4, interfaces can include properties, but only hooked properties (properties with `get` and/or `set` hooks). A plain property without hooks is not allowed in an interface because interfaces cannot dictate how the implementing class stores its data -- they can only define the access contract.

This is a PHP language-level restriction enforced since PHP 8.4 property hooks.

## How to fix it

Add hooks to the property to define the access contract:

```diff-php
 interface HasName
 {
-	public string $name;
+	public string $name { get; set; }
 }
```

If only read access is needed:

```diff-php
 interface HasName
 {
-	public string $name;
+	public string $name { get; }
 }
```
