---
title: "property.abstractWithoutAbstractHook"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

abstract class Shape
{
	abstract public string $name {
		get {
			return 'shape';
		}
		set;
	}
}
```

## Why is it reported?

The property is declared as `abstract` and has hooks, but none of its hooks are actually abstract (without a body). For a property to be declared abstract, it must specify at least one abstract hook -- a hook declared without a body. If all hooks have bodies, the property is not abstract.

This is a PHP language-level restriction enforced since PHP 8.4 property hooks.

## How to fix it

Make at least one hook abstract by removing its body:

```diff-php
 abstract class Shape
 {
 	abstract public string $name {
-		get {
-			return 'shape';
-		}
+		get;
 		set;
 	}
 }
```

Alternatively, remove the `abstract` keyword from the property if all hooks should have implementations:

```diff-php
-abstract class Shape
+class Shape
 {
-	abstract public string $name {
+	public string $name {
 		get {
 			return 'shape';
 		}
+		set {
+			$this->name = $value;
+		}
 	}
 }
```
