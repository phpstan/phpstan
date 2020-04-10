---
title: "Union Types vs. Intersection Types"
date: 2017-11-27
tags: guides
---

One of the headlining features of [PHPStan 0.9](/blog/phpstan-0-9-a-huge-leap-forward) is the introduction of intersection types. Since this is a very useful feature that helps us understand the code much better, but the terminology is largely unknown and mysterious to the PHP community, I decided to write up and compare these two kinds of compound types.

# Union Types

- Written as `Foo|Bar`. Common syntax used in phpDocs for many years.
- It means that the type of a variable is either Foo or Bar.
- When a function accepts `Foo|Bar` as an argument, it means that only properties and methods that are available on both Foo and Bar can be safely accessed on the variable. If Foo has a `$loremProperty`, you can access it only if Bar has the same property.
- It's dangerous to pass value of type `Foo|Bar` to a function parameter where only Foo is allowed, because if it's Bar in runtime, the called function does not accept it.
- It's fine to pass value of type Foo to a function parameter of type `Foo|Bar`, because Foo is one of more allowed options.

The most common way of utilising union types is in function arguments that accept multiple different types. They can be compared with instanceof or a comparison operator to trim down the possibilities when working with them:

```php
/**
 * @param Foo|Bar $object
 */
public function doSomethingUseful($object)
{
    if ($object instanceof Foo) {
        // now we can be sure that $object is just Foo in this branch
    } elseif ($object instanceof Bar) {
        // dtto for Bar
    }
}
```

Do not confuse union types with the way developers usually mark types of items in a collection (that's usually an iterator):

```php
/**
 * @param Collection|Foo[] $object
 */
public function doSomethingUseful($object)
{
    foreach ($object as $foo) {
        // $foo is Foo here
    }
}
```

# Intersection Types

- Written as `Foo&Bar`. This is a very rare syntax seen in the wild and it might not even be supported by your IDE. It's because when people write `Foo|Bar`, they sometimes mean a union type and sometimes an intersection type by that. Let's hope that this changes in the following months and years (not only) thanks to PHPStan. Differentiating between them helps the code to be more understandable and also more friendly to static analyzers which benefits everyone.

- It means that the type of a variable is Foo and Bar at the same time.

- When a function accepts `Foo&Bar` as an argument, it means that properties and methods from either type can be safely accessed. You can access properties and call methods from Foo even if they are not on Bar.

- It's fine to pass value of type `Foo&Bar` to a parameter of type Foo.

- It's dangerous to pass value of type Foo to a parameter of type `Foo&Bar`, because someone might access a property or call a method from Bar.

- It's fine to pass value of type `Foo&Bar&Baz` to a parameter of type `Foo&Bar`, type Baz is simply thrown away in this case.

The thing is that you're already creating intersection types in your code and not even realize it! Consider this code:

```php
public function doSomethingUseful(Foo $object)
{
    if ($object instanceof BarInterface) {
        // $foo is Foo&BarInterface here!
    }
}
```

Other use case for them are mock objects in unit-testing frameworks, typically PHPUnit. Think about it: you create an object and it's possible to call methods on it from both mocked class and mock-specific configuration methods!

```php
class Foo
{
    public function doFooStuff()
    {
    }
}

class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function testSomething()
    {
        $mockedFoo = $this–>createMock(Foo::class);
        // $mockedFoo is Foo&PHPUnit_Framework_MockObject_MockObject

        // we can call mock-configuration methods:
        $mockedFoo->method('doFooStuff')
            ->will($this->returnValue('fooResult'));

        // and also methods from Foo itself:
        $mockedFoo->doFooStuff();
    }
}
```

You can also take advantage of intersection types if you don't want to tie your code to a specific class, but want to typehint multiple interfaces at once. For example, if you want to safely iterate over an object and at the same time pass it to `count()`, you can do it like this:

```php
/**
 * @param \Traversable&\Countable $object
 */
public function doSomethingUseful($object)
{
    echo sprintf(
        'We are going to iterate over %d values!',
        count($object)
    );
    foreach ($object as $foo) {

    }
}
```

This is really nice because it supports designing your codebase with small and simple interfaces.
