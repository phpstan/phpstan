---
title: "property.uninitializedReadonly"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	private readonly int $assigned;

	private readonly int $unassigned;

	public function __construct()
	{
		$this->assigned = 1;
	}
}
```

## Why is it reported?

A `readonly` property must be initialized exactly once during the object's construction. If a readonly property is not assigned in the constructor (or in a method configured as an [additional constructor](/config-reference#additionalconstructors)), it will remain uninitialized. Accessing an uninitialized readonly property at runtime throws an `Error`.

In the example above, `$unassigned` is declared as `readonly` but is never assigned in the constructor, so it will remain uninitialized.

This error is also reported when a readonly property is accessed before it has been assigned within the constructor, since the property is still in an uninitialized state at that point.

## How to fix it

Assign the readonly property in the constructor:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	private readonly int $assigned;

 	private readonly int $unassigned;

 	public function __construct()
 	{
 		$this->assigned = 1;
+		$this->unassigned = 2;
 	}
 }
```

Or use constructor promotion to ensure the property is always initialized:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	private readonly int $assigned;
-
-	private readonly int $unassigned;
-
-	public function __construct()
-	{
-		$this->assigned = 1;
-	}
+	public function __construct(
+		private readonly int $assigned,
+		private readonly int $unassigned,
+	) {}
 }
```

If the property is initialized in a method other than `__construct`, configure that method as an additional constructor using the [`additionalConstructors`](/config-reference#additionalconstructors) configuration parameter.
