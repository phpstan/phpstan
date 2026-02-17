---
title: "property.readOnlyByPhpDocDefaultValue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/** @readonly */
	public int $value = 0;
}
```

## Why is it reported?

A property marked with `@readonly` (or `@phpstan-readonly`) in PHPDoc has a default value. Readonly properties should only be initialized in the constructor, not with a default value in the property declaration.

## How to fix it

Remove the default value and initialize the property in the constructor:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	/** @readonly */
-	public int $value = 0;
+	public int $value;
+
+	public function __construct()
+	{
+		$this->value = 0;
+	}
 }
```
