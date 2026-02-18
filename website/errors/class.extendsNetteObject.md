---
title: "class.extendsNetteObject"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use Nette\LegacyObject;

class MyPresenter extends LegacyObject
{
}
```

## Why is it reported?

The `Nette\Object` and `Nette\LegacyObject` base classes are deprecated in the Nette framework. Extending them is no longer recommended. The modern approach is to use the `Nette\SmartObject` trait instead, which provides the same magic functionality (property accessors, event support) without requiring class inheritance.

## How to fix it

Replace the base class with the `Nette\SmartObject` trait:

```diff-php
 <?php declare(strict_types = 1);

-use Nette\LegacyObject;
+use Nette\SmartObject;

-class MyPresenter extends LegacyObject
+class MyPresenter
 {
+	use SmartObject;
 }
```
