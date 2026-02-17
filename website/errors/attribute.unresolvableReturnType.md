---
title: "attribute.unresolvableReturnType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

#[\Attribute]
class MyAttribute
{
    /**
     * @template T
     * @param T $value
     * @return T
     */
    public function __construct(public mixed $value)
    {
    }
}

#[MyAttribute(value: 'hello')]
class Foo {}
```

## Why is it reported?

The constructor of the attribute class uses generic template types in its return type, but PHPStan cannot resolve those template types based on the arguments passed in the attribute. This typically happens when the constructor's return type contains a template type parameter that cannot be inferred from the provided arguments.

Since PHP attributes are instantiated by the runtime with limited context, unresolvable template types in the constructor's return type indicate a potential issue with the generic type definition.

## How to fix it

The unresolvable template type is usually a symptom of a not-precise-enough type being passed to the constructor. Pass a more specific type so that PHPStan can resolve the template:

```diff-php
-#[MyAttribute(value: 'hello')]
+#[MyAttribute(value: new ConcreteValue('hello'))]
 class Foo {}
```

If the template type on the constructor is not needed, simplify the type signature:

```diff-php
 #[\Attribute]
 class MyAttribute
 {
-    /**
-     * @template T
-     * @param T $value
-     * @return T
-     */
-    public function __construct(public mixed $value)
+    public function __construct(public string $value)
     {
     }
 }
```
