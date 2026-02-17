---
title: "interface.allowDynamicProperties"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

#[\AllowDynamicProperties]
interface Foo
{
	public function getName(): string;
}
```

## Why is it reported?

The `#[\AllowDynamicProperties]` attribute cannot be used with interfaces. Interfaces define contracts for classes to implement and cannot have properties of their own, so allowing dynamic properties on an interface is meaningless. PHP will emit a fatal error at runtime.

## How to fix it

Remove the `#[\AllowDynamicProperties]` attribute from the interface:

```diff-php
 <?php declare(strict_types = 1);

-#[\AllowDynamicProperties]
 interface Foo
 {
 	public function getName(): string;
 }
```

If implementing classes need dynamic properties, apply the attribute to those classes instead:

```diff-php
 <?php declare(strict_types = 1);

 interface Foo
 {
 	public function getName(): string;
 }

+#[\AllowDynamicProperties]
+class Bar implements Foo
+{
+	public function getName(): string
+	{
+		return 'bar';
+	}
+}
```
