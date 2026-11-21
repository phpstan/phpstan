---
title: "property.backedGetHookByRef"
shortDescription: "Get hook of a backed property with a set hook returns by reference."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class ParentClass
{
	public int $prop = 1;
}

class ChildClass extends ParentClass
{
	private int $storage = 1;

	public int $prop {
		&get => $this->storage;
		set {
			$this->storage = $value;
		}
	}
}
```

## Why is it reported?

A backed property (a property with its own storage, here inherited from the parent class) has both a `set` hook and a `get` hook that returns by reference. A reference returned from `&get` would allow writing to the property without going through the `set` hook, bypassing its logic. PHP does not allow this combination and reports a fatal error when the class is declared.

## How to fix it

Return by value from the `get` hook:

```diff-php
 <?php declare(strict_types = 1);

 class ChildClass extends ParentClass
 {
 	private int $storage = 1;

 	public int $prop {
-		&get => $this->storage;
+		get => $this->storage;
 		set {
 			$this->storage = $value;
 		}
 	}
 }
```

Or remove the `set` hook if the property should be modified through the returned reference:

```diff-php
 <?php declare(strict_types = 1);

 class ChildClass extends ParentClass
 {
 	private int $storage = 1;

 	public int $prop {
 		&get => $this->storage;
-		set {
-			$this->storage = $value;
-		}
 	}
 }
```
