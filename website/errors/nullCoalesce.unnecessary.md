---
title: "nullCoalesce.unnecessary"
shortDescription: "The ?? or ??= operator is redundant because the left side is always set and the right side is null."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function alwaysDefinedNullableParam(?string $name): ?string
{
	return $name ?? null;
}
```

## Why is it reported?

The `??` (null coalesce) operator returns its right side only when the left side is either undefined or `null`. When the right side is itself `null`, the operator can only ever produce `null` in that case -- exactly the value the left side already holds. Combined with a left side that is always defined, the whole `?? null` never changes the result and is dead code.

In the example, `$name` is a parameter, so it is always set, and its value is already `?string`. Writing `$name ?? null` returns `$name` when it is a string and `null` when it is `null` -- which is the same as just returning `$name`.

The same applies to `??= null`, which assigns `null` only when the target is undefined or already `null`, and so never changes anything when the target is always set.

This check is part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Remove the redundant `?? null`:

```diff-php
 function alwaysDefinedNullableParam(?string $name): ?string
 {
-	return $name ?? null;
+	return $name;
 }
```

Remove the redundant `??= null` assignment:

```diff-php
 function assignCoalesceAlwaysSet(?string $name): void
 {
 	$x = $name;
-	$x ??= null;
 }
```

If the intent was to fall back to a non-null default, use that value on the right side instead:

```diff-php
 function alwaysDefinedNullableParam(?string $name): string
 {
-	return $name ?? null;
+	return $name ?? 'default';
 }
```
