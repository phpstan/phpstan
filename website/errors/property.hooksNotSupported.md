---
title: "property.hooksNotSupported"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public string $name {
		get => $this->name;
		set => $this->name = $value;
	}
}
```

## Why is it reported?

Property hooks (get/set hooks) are only supported on PHP 8.4 and later. The analysed code uses property hooks, but the project's configured PHP version is older than 8.4.

## How to fix it

If your project needs to support PHP versions older than 8.4, use getter and setter methods instead:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public string $name {
-		get => $this->name;
-		set => $this->name = $value;
-	}
+	private string $name;
+
+	public function getName(): string
+	{
+		return $this->name;
+	}
+
+	public function setName(string $value): void
+	{
+		$this->name = $value;
+	}
 }
```

Or update the PHP version requirement for your project to PHP 8.4 or later.
