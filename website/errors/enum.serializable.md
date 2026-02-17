---
title: "enum.serializable"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Suit implements \Serializable
{
    case Hearts;
    case Diamonds;

    public function serialize(): string
    {
        return $this->name;
    }

    public function unserialize(string $data): void
    {
    }
}
```

## Why is it reported?

PHP enums cannot implement the `Serializable` interface. This is a language-level restriction because enums have their own built-in serialization mechanism. Attempting to implement `Serializable` on an enum produces a fatal error.

## How to fix it

Remove the `Serializable` interface. Enums are serializable by default through PHP's native mechanism:

```diff-php
 <?php declare(strict_types = 1);

-enum Suit implements \Serializable
+enum Suit
 {
     case Hearts;
     case Diamonds;
-
-    public function serialize(): string
-    {
-        return $this->name;
-    }
-
-    public function unserialize(string $data): void
-    {
-    }
 }
```
