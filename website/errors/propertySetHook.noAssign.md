---
title: "propertySetHook.noAssign"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public int $count {
		get => $this->count;
		set {
			echo "Setting count to $value";
		}
	}
}
```

## Why is it reported?

A `set` hook on a non-virtual (backed) property is expected to assign a value to the backing property. If the hook body never assigns to `$this->propertyName`, the property value does not change when set is called, which is almost certainly a bug.

## How to fix it

Assign the value to the property inside the `set` hook:

```diff-php
 public int $count {
 	get => $this->count;
 	set {
 		echo "Setting count to $value";
+		$this->count = $value;
 	}
 }
```

Or if the property is intentionally virtual (no backing store), remove the `get` hook that references `$this->count` so the property becomes fully virtual.
