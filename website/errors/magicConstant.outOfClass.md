---
title: "magicConstant.outOfClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

echo __CLASS__;
```

## Why is it reported?

The magic constant `__CLASS__` is used outside of a class definition, where it always evaluates to an empty string `''`. This is almost never the intended behaviour and usually indicates that the code was moved out of a class without updating the constant reference.

## How to fix it

Move the code inside a class, or use a different way to get the class name:

```diff-php
-echo __CLASS__;
+class Foo
+{
+	public function printName(): void
+	{
+		echo __CLASS__; // outputs "Foo"
+	}
+}
```

If the code is in a function and needs the caller's class name, accept it as a parameter:

```diff-php
-function logContext(): void
+function logContext(string $className): void
 {
-	echo __CLASS__;
+	echo $className;
 }
```
