---
title: "staticMethod.resultDiscarded"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Parser
{
    #[\NoDiscard]
    public static function parse(string $input): array
    {
        return explode(',', $input);
    }
}

Parser::parse('a,b,c');
```

## Why is it reported?

The static method is marked with the `#[\NoDiscard]` attribute (available since PHP 8.5), which indicates that its return value must not be ignored. Calling the method on a separate line without using the return value means the result is being discarded, which is likely a mistake.

The `#[\NoDiscard]` attribute is used for methods where the return value is the primary purpose of the call, and ignoring it means the call serves no useful purpose.

## How to fix it

Use the return value of the method call:

```diff-php
 <?php declare(strict_types = 1);

-Parser::parse('a,b,c');
+$result = Parser::parse('a,b,c');
```

If the return value is intentionally not needed, use a `(void)` cast to explicitly acknowledge the discard:

```diff-php
 <?php declare(strict_types = 1);

-(void) Parser::parse('a,b,c');
+$result = Parser::parse('a,b,c');
```
