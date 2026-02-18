---
title: "new.internalInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
interface Handler
{
}

class DefaultHandler implements Handler
{
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\DefaultHandler;

$handler = new DefaultHandler();
```

## Why is it reported?

The `new` expression instantiates a class that implements an interface marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Instantiating classes that depend on internal interfaces creates a fragile dependency on implementation details that can change without notice.

## How to fix it

Use a public (non-internal) type instead. Check whether the library provides a public factory method or a public interface for the same purpose:

```diff-php
-$handler = new DefaultHandler();
+$handler = HandlerFactory::create();
```

If you control the internal interface, consider making it public or providing a public alternative.
