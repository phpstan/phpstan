---
title: "propertyGetHook.noRead"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class User
{

	public string $fullName {
		get {
			return 'John Doe';
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

The `get` hook of a non-virtual property does not read the property's backing value. A non-virtual property (one that has a backing store) has its own stored value, and the `get` hook is expected to read that value (using `$this->propertyName` or `$field`). If the `get` hook returns a completely independent value without reading the property, the stored value becomes unused and the property should likely be virtual (without a backing value) instead.

## How to fix it

Read the property's value in the `get` hook:

```diff-php
 <?php declare(strict_types = 1);

 class User
 {

 	public string $fullName {
 		get {
-			return 'John Doe';
+			return $this->fullName;
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

Or make the property virtual by removing the backing value and using other properties:

```diff-php
 <?php declare(strict_types = 1);

 class User
 {

 	public string $fullName {
 		get {
-			return 'John Doe';
+			return $this->firstName . ' ' . $this->lastName;
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
