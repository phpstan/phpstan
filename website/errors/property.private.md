---
title: "property.private"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class ParentClass
{
	private string $secret = 'hidden';
}

class ChildClass extends ParentClass
{
	public function getSecret(): string
	{
		return $this->secret; // ERROR: Access to private property $secret of parent class ParentClass.
	}
}
```

## Why is it reported?

The code attempts to access a `private` property from outside the class where it is declared. Private properties are only accessible within the class that defines them -- not from subclasses, not from parent classes, and not from outside code. Attempting to access a private property from a child class will result in accessing an undefined property rather than the parent's private property.

## How to fix it

Change the property visibility to `protected` if it should be accessible by subclasses:

```diff-php
 <?php declare(strict_types = 1);

 class ParentClass
 {
-	private string $secret = 'hidden';
+	protected string $secret = 'hidden';
 }

 class ChildClass extends ParentClass
 {
 	public function getSecret(): string
 	{
 		return $this->secret;
 	}
 }
```

Or provide a getter method in the parent class:

```diff-php
 <?php declare(strict_types = 1);

 class ParentClass
 {
 	private string $secret = 'hidden';
+
+	protected function getSecret(): string
+	{
+		return $this->secret;
+	}
 }

 class ChildClass extends ParentClass
 {
 	public function getSecret(): string
 	{
-		return $this->secret;
+		return parent::getSecret();
 	}
 }
```
