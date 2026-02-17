---
title: "property.abstractInInterface"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface HasName
{
	abstract public string $name { get; } // ERROR: Property in interface cannot be explicitly abstract.
}
```

## Why is it reported?

Properties declared in interfaces are implicitly abstract, similar to how interface methods are implicitly abstract. Adding the `abstract` keyword explicitly is redundant and not allowed by PHP. All interface properties with hooks are already required to be implemented by classes that implement the interface.

## How to fix it

Remove the `abstract` keyword from the property declaration:

```diff-php
 <?php declare(strict_types = 1);

 interface HasName
 {
-	abstract public string $name { get; }
+	public string $name { get; }
 }
```
