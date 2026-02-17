---
title: "parameterByRef.tooWideBool"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function setFlag(bool &$flag): void
{
	$flag = true;
}
```

## Why is it reported?

The function declares a by-reference parameter with type `bool`, but only ever assigns `true` to it. The `false` value is never assigned, making the `bool` type wider than necessary.

## How to fix it

If the parameter is always set to a specific boolean value, narrow the by-ref type using a `@param-out` PHPDoc tag:

```diff-php
 <?php declare(strict_types = 1);

+/** @param-out true $flag */
 function setFlag(bool &$flag): void
 {
 	$flag = true;
 }
```

Alternatively, if both `true` and `false` should be possible, ensure both values are actually assigned on different code paths:

```php
<?php declare(strict_types = 1);

function setFlag(bool &$flag, bool $condition): void
{
	if ($condition) {
		$flag = true;
	} else {
		$flag = false;
	}
}
```
