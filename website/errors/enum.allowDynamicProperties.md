---
title: "enum.allowDynamicProperties"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

#[\AllowDynamicProperties]
enum Suit
{
	case Hearts;
	case Diamonds;
	case Clubs;
	case Spades;
}
```

## Why is it reported?

The `#[\AllowDynamicProperties]` attribute cannot be used with enums. Enums in PHP do not support dynamic properties -- they are value types with a fixed set of cases and cannot have instance properties assigned at runtime. This is a language-level restriction enforced by PHP itself.

## How to fix it

Remove the `#[\AllowDynamicProperties]` attribute from the enum:

```diff-php
-#[\AllowDynamicProperties]
 enum Suit
 {
 	case Hearts;
 	case Diamonds;
 	case Clubs;
 	case Spades;
 }
```
