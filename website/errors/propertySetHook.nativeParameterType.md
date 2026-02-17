---
title: "propertySetHook.nativeParameterType"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public string $name {
		set(int $value) { // ERROR: Native type int of set hook parameter $value is not contravariant with native type string of property Foo::$name.
			$this->name = (string) $value;
		}
	}
}
```

## Why is it reported?

The native type of the `set` hook parameter must be compatible with the property's native type. Specifically, the parameter type must be contravariant with (a supertype of) the property type. This ensures type safety -- any value that the property can hold must also be accepted by the set hook.

This error is also reported when:
- The set hook parameter has a native type but the property does not
- The property has a native type but the set hook parameter does not

## How to fix it

Make the set hook parameter type match or be a supertype of the property type:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public string $name {
-		set(int $value) {
-			$this->name = (string) $value;
+		set(string $value) {
+			$this->name = $value;
 		}
 	}
 }
```

Or widen the set hook parameter type to accept the property type:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public string $name {
-		set(int $value) {
-			$this->name = (string) $value;
+		set(string|int $value) {
+			$this->name = (string) $value;
 		}
 	}
 }
```
