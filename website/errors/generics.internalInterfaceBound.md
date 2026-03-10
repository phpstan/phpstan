---
title: "generics.internalInterfaceBound"
shortDescription: "Template type bound references an internal interface."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace Vendor {
	/** @internal */
	interface InternalInterface {}
}

namespace App {
	/**
	 * @template T of \Vendor\InternalInterface
	 */
	class Container {}
}
```

## Why is it reported?

A template type bound references an interface that is marked as `@internal`. The `@template T of SomeInterface` syntax constrains the template type to subtypes of the given interface. When that interface is internal, using it as a bound creates a dependency on an implementation detail that may change without notice.

## How to fix it

Use a public interface as the template type bound instead:

```diff-php
 /**
- * @template T of \Vendor\InternalInterface
+ * @template T of \Vendor\PublicInterface
  */
 class Container {}
```

If no suitable public interface exists, consider removing the bound or contacting the library maintainers:

```diff-php
 /**
- * @template T of \Vendor\InternalInterface
+ * @template T
  */
 class Container {}
```
