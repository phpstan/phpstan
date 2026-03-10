---
title: "propertyGetHook.noRead"
shortDescription: "Get hook of a non-virtual property does not read the backing value."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class User
{

	public string $fullName {
		get {
			return "John Doe";
		}
		set {
			$this->fullName = $value;
		}
	}

	public function __construct(
		public string $firstName,
		public string $lastName,
	)
	{
	}

}
```

## Why is it reported?

The `get` hook of a non-virtual property does not read the property's backing value. A non-virtual property (one that has a backing store because it has a `set` hook that writes to `$this->propertyName`) has its own stored value, and the `get` hook is expected to read that value. If the `get` hook returns a completely independent value without reading the property, the stored value becomes unused and the property should likely be virtual (without a backing value) instead.

In the example above, the `set` hook writes to `$this->fullName`, making the property non-virtual. But the `get` hook ignores the stored value and returns a hardcoded string.

## How to fix it

Read the property's value in the `get` hook:

```diff-php
 class User
 {

 	public string $fullName {
 		get {
-			return "John Doe";
+			return $this->fullName;
 		}
 		set {
 			$this->fullName = $value;
 		}
 	}

 	public function __construct(
 		public string $firstName,
 		public string $lastName,
 	)
 	{
 	}

 }
```

Or make the property virtual by removing the `set` hook and computing the value from other properties:

```diff-php
 class User
 {

 	public string $fullName {
 		get {
-			return "John Doe";
+			return $this->firstName . ' ' . $this->lastName;
 		}
-		set {
-			$this->fullName = $value;
-		}
 	}

 	public function __construct(
 		public string $firstName,
 		public string $lastName,
 	)
 	{
 	}

 }
```
