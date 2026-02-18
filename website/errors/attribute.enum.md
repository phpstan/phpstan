---
title: "attribute.enum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

#[\Attribute]
enum Suit
{
	case Hearts;
	case Diamonds;
}
```

## Why is it reported?

PHP attributes can only be applied to classes (not abstract). An enum cannot be used as an `#[\Attribute]` class because PHP requires attribute classes to be non-abstract, non-enum, non-interface, non-trait classes. Marking an enum with `#[\Attribute]` has no effect and would cause an error if the enum were ever used as an attribute on another declaration.

## How to fix it

Convert the attribute to a regular class:

```diff-php
 <?php declare(strict_types = 1);

-#[\Attribute]
-enum Suit
+#[\Attribute]
+class Suit
 {
-	case Hearts;
-	case Diamonds;
+	public function __construct(
+		public readonly string $value,
+	) {
+	}
 }
```
