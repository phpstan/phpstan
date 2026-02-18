---
title: "catch.internalInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// Assuming SomeLibrary\InternalException is marked @internal
try {
    someLibraryCall();
} catch (\SomeLibrary\InternalException $e) {
    // ...
}
```

## Why is it reported?

The `catch` block references an interface (or class) that is marked as `@internal` by its declaring library. Internal types are implementation details not meant for use by external code. Depending on internal types in `catch` blocks makes the code fragile because the library may rename, remove, or restructure those types without notice.

## How to fix it

Catch the public exception type that the library exposes instead:

```diff-php
 <?php declare(strict_types = 1);

 try {
     someLibraryCall();
-} catch (\SomeLibrary\InternalException $e) {
+} catch (\SomeLibrary\PublicException $e) {
     // ...
 }
```

Or catch a broader, public exception type:

```diff-php
 <?php declare(strict_types = 1);

 try {
     someLibraryCall();
-} catch (\SomeLibrary\InternalException $e) {
+} catch (\RuntimeException $e) {
     // ...
 }
```
