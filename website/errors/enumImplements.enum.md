---
title: "enumImplements.enum"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Color
{
	case Red;
}

enum Shape implements Color
{
	case Circle;
}
```

## Why is it reported?

An enum uses `implements` to reference another enum instead of an interface. In PHP, enums can only implement interfaces. An enum cannot implement another enum.

## How to fix it

If both enums need to share a common contract, create an interface:

```diff-php
 <?php declare(strict_types = 1);

+interface HasLabel
+{
+	public function label(): string;
+}
+
-enum Color
+enum Color implements HasLabel
 {
 	case Red;
+
+	public function label(): string
+	{
+		return $this->name;
+	}
 }

-enum Shape implements Color
+enum Shape implements HasLabel
 {
 	case Circle;
+
+	public function label(): string
+	{
+		return $this->name;
+	}
 }
```
