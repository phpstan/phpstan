---
title: "property.abstract"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo // ERROR: Non-abstract classes cannot include abstract properties.
{
	abstract public int $value { get; }
}
```

## Why is it reported?

PHP does not allow abstract properties in non-abstract classes. An abstract property declares a hook (like `get` or `set`) without providing an implementation, which means subclasses must provide the implementation. This only makes sense in abstract classes, where subclasses are required to implement the missing parts.

This is a PHP language-level restriction enforced since PHP 8.4, which introduced property hooks.

## How to fix it

Either make the class abstract:

```diff-php
 <?php declare(strict_types = 1);

-class Foo
+abstract class Foo
 {
 	abstract public int $value { get; }
 }
```

Or provide a body for the property hook:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	abstract public int $value { get; }
+	public int $value {
+		get => $this->value;
+	}
 }
```
