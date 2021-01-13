---
title: 'Solving PHPStan error "Unsafe usage of new static()"'
date: 2021-01-13
tags: guides
---

This error is reported for `new static()` calls that might break once the class is extended, and the constructor is overriden with different parameters.

Consider this example:

```php
<?php declare(strict_types = 1);

class Foo
{

    public function __construct(int $i) { }
    
    public function doFoo(): void
    {
        new static(1); // PHPStan reports: Unsafe usage of new static()
    }

}

class Bar extends Foo
{
    
    public function __construct(string $s) { }
    
}

(new Foo(1))->doFoo(); // works, returns Foo
(new Bar('s'))->doFoo(); // crashes with: Argument #1 ($s) must be of type string, int given
```

So the PHPStan rule protects you from accidentally breaking your code when extending the class and defining a different constructor.

Let's go over possible solutions:

Make the class final
========================

This is the easiest course of action - you might realize you actually don't want the class to be extended and it's been open for inheritance by mistake. Once you make the class final, the error is no longer reported. At that point you can also change `new static()` and make it just `new self()` without changing the functionality.

```php
final class Foo
{
    ...
}
```

Make the constructor final
========================

If you want the class open for extension, you can disallow overriding the constructor in child classes and make the code safe that way.

```php
final public function __construct(int $i) { }
```

Make the constructor abstract
========================

Signatures of abstract constructors are enforced in child classes. The disadvantage of this approach is that the child class must define its own constructor, it cannot inherit the parent one.

```php
abstract public function __construct(int $i);
```

Enforce constructor signature through an interface
========================

Making the constructor abstract can be also achieved by an interface while keeping the implementation in the parent class. The child class don't have to define their own constructor, they'll inherit the parent one, but in case they define their own constructor, its signature will be enforced by PHP (and PHPStan).

```php
interface FooInterface
{
    public function __construct(int $i);
}

class Foo implements FooInterface
{

    public function __construct(int $i) { ... }
    
    ...
```
