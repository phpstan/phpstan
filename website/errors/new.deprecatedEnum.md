---
title: "new.deprecatedEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use Status instead */
enum OldStatus
{
	case Active;
	case Inactive;
}

/**
 * @method OldStatus getStatus()
 */
class Foo
{
	// error: Instantiation of deprecated enum OldStatus.
}
```

## Why is it reported?

A deprecated enum is being referenced in a context related to object instantiation (such as a `@method` tag, type reference in a `new` context, etc.). The enum has been marked with `@deprecated`, indicating it should no longer be used and may be removed in a future version.

## How to fix it

Replace references to the deprecated enum with its recommended replacement.

```diff-php
-/** @deprecated Use Status instead */
-enum OldStatus
-{
-	case Active;
-	case Inactive;
-}

 enum Status
 {
 	case Active;
 	case Inactive;
 }

 /**
- * @method OldStatus getStatus()
+ * @method Status getStatus()
  */
 class Foo
 {
 }
```
