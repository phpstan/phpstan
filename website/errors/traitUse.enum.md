---
title: "traitUse.enum"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Color: string
{
	case Red = 'red';
	case Green = 'green';
	case Blue = 'blue';
}

class Palette
{
	use Color;
}
```

## Why is it reported?

The `use` statement inside a class body is attempting to use an enum as if it were a trait. The `use` keyword in a class body is reserved exclusively for traits. Enums cannot be used with the `use` statement. This code will result in a fatal error at runtime.

## How to fix it

If the intent is to use the enum type, reference it as a property type or parameter type instead:

```diff-php
 <?php declare(strict_types = 1);

 class Palette
 {
-	use Color;
+	/** @var list<Color> */
+	private array $colors = [];
+
+	public function addColor(Color $color): void
+	{
+		$this->colors[] = $color;
+	}
 }
```

If the intent is to share methods across classes, extract a trait:

```diff-php
 <?php declare(strict_types = 1);

+trait ColorHelpers
+{
+	public function getDefaultColor(): Color
+	{
+		return Color::Red;
+	}
+}
+
 class Palette
 {
-	use Color;
+	use ColorHelpers;
 }
```
