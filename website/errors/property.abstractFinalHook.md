---
title: "property.abstractFinalHook"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface HasName
{
	public string $name {
		final get;
	}
}
```

## Why is it reported?

A property hook cannot be both abstract and final. In an interface, all hooks without bodies are implicitly abstract. Marking such a hook as `final` creates a contradiction: the hook must be implemented by a subclass (abstract) but cannot be overridden (final). PHP rejects this at compile time.

## How to fix it

Remove the `final` modifier from the hook:

```diff-php
 interface HasName
 {
 	public string $name {
-		final get;
+		get;
 	}
 }
```
