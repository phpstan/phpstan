---
title: "property.readOnlyDefaultValue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Config
{

	public readonly string $name = 'default';

}
```

## Why is it reported?

A `readonly` property cannot have a default value. PHP does not allow readonly properties to be initialized with a default value in their declaration because a readonly property can only be written once, and that write must happen explicitly (typically in the constructor). Allowing a default value would conflict with the semantics of readonly, which guarantees the property is initialized at a single point in code.

## How to fix it

Move the initialization to the constructor:

```diff-php
 <?php declare(strict_types = 1);

 class Config
 {

-	public readonly string $name = 'default';
+	public readonly string $name;
+
+	public function __construct()
+	{
+		$this->name = 'default';
+	}

 }
```

Or use a promoted constructor parameter:

```diff-php
 <?php declare(strict_types = 1);

 class Config
 {

-	public readonly string $name = 'default';
-
+	public function __construct(
+		public readonly string $name = 'default',
+	)
+	{
+	}

 }
```
