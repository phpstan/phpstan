---
title: "property.readOnlyAssignNotOnThis"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	private readonly int $bar;

	public function __construct(int $bar)
	{
		$self = new self(1);
		$self->bar = $bar;
	}
}
```

## Why is it reported?

PHP's `readonly` properties can only be initialized once, and only from within the scope of the declaring class's constructor. Additionally, the initialization must happen on `$this` -- not on any other instance of the class. Assigning a readonly property on a different instance (even of the same class) inside the constructor is not allowed.

In the example above, `$self->bar = $bar` assigns the readonly property on a newly created `$self` instance rather than on `$this`, which violates the readonly property contract.

## How to fix it

Assign the readonly property on `$this` instead of on another instance:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	private readonly int $bar;

 	public function __construct(int $bar)
 	{
-		$self = new self(1);
-		$self->bar = $bar;
+		$this->bar = $bar;
 	}
 }
```

Or pass the value through the constructor of the other instance so each instance initializes its own readonly property:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	private readonly int $bar;

 	public function __construct(int $bar)
 	{
-		$self = new self(1);
-		$self->bar = $bar;
+		$this->bar = $bar;
+		$self = new self(1); // $self->bar is set via its own constructor
 	}
 }
```
