---
title: "property.virtualDefault"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class User
{
	public string $fullName = 'Unknown' {
		get => $this->firstName . ' ' . $this->lastName;
	}

	public string $firstName = 'John';
	public string $lastName = 'Doe';
}
```

## Why is it reported?

A virtual hooked property has no backing storage -- it computes its value through hooks. Assigning a default value to such a property is not allowed because there is no field to store it. PHP rejects this at compile time.

A property is virtual when all of its hooks are defined and none of them reference `$this->propertyName` for the property itself.

## How to fix it

Remove the default value from the virtual property:

```diff-php
-public string $fullName = 'Unknown' {
+public string $fullName {
 	get => $this->firstName . ' ' . $this->lastName;
 }
```

Or add a backing store by including a `set` hook that assigns to `$this->fullName`:

```diff-php
 public string $fullName = 'Unknown' {
 	get => $this->firstName . ' ' . $this->lastName;
+	set => $this->fullName = $value;
 }
```
