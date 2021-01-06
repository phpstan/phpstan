---
title: "Generics in PHP using PHPDocs"
date: 2019-12-02
tags: guides
ogImage: /images/generics.jpg
---

![PHP code](/images/generics.jpg)

Two years ago I wrote an impactful article on [union and intersection types](/blog/union-types-vs-intersection-types). It helped the PHP community to familiarize themselves with these concepts which eventually led to intersection types [support in PhpStorm](https://blog.jetbrains.com/phpstorm/2018/09/phpstorm-2018-3-eap-183-2635-12/).

I wrote that article because the differences between unions and intersections are useful and important for static analysis, and developers should be aware of them.

Today I have a similar goal. Generics are coming to PHPStan 0.12 later this week, I want to explain what they’re all about, and get everyone excited.

## Infinite number of signatures

When we’re declaring a function, we’re used to attach a single signature to it. There’s no other option. So we declare that the function accepts an argument of a specific type, and also returns a specific type:

```php
/**
 * @param string $param
 * @return string
 */
function foo($param)
{
	...
}
```

These types are set in stone. If you have a function that returns different types based on argument types passed when calling the function, you’d have to resort to returning a union type, or a more general type like object or mixed:

```php
function findEntity(string $className, int $id): ?object
{
	// finds and returns an entity object based on $className
	// and primary key in $id
}
```

This is not ideal for static analysis. It’s not enough information to keep the code type-safe. We always want to know the exact type. And that’s what generics are for. They offer generating infinite number of signatures for functions and methods based on rules developers can define themselves.

## Type variables

These rules are defined using type variables. Other languages that have generics also use this term. In PHPDocs we annotate them with the `@template` tag. Consider a function that returns the same type it accepts:

```php
/**
 * @template T
 * @param T $a
 * @return T
 */
function foo($a)
{
	return $a;
}
```

The type variable name can be anything, as long as you don’t use an existing class name.

You can also limit which types can be used in place of the type variable with an upper bound using the `of` keyword:

```php
/**
 * @template T of \Exception
 * @param T $exception
 * @return T
 */
function foo($exception)
{
	...
}
```

Only objects of classes extending Exception will be accepted and returned by this function.

## Class names

If you want to involve a class name in the type resolution, you can use the `class-string` pseudotype for that:

```php
/**
 * @template T
 * @param class-string<T> $className
 * @param int $id
 * @return T|null
 */
function findEntity(string $className, int $id)
{
	// ...
}
```

If you then call `findEntity(Article::class, 1)`, PHPStan will know that you’re getting an Article object or null!

Marking the return type as `T[]`(for example for a `findAll()`function) would infer the return type as an array of Articles.

## Class-level generics

Up until this point, I’ve written only about function-level or method-level generics. We can also put `@template` above a class or an interface:

```php
/**
 * @template T
 */
interface Collection
{
}
```

And then reference the type variable above properties and methods:

```php
/**
 * @param T $item
 */
public function add($item): void;

/**
 * @return T
 */
public function get(int $index);
```

The types of the Collection can be specified when you’re typehinting it somewhere else:

```php
/**
 * @param Collection<Dog> $dogs
 */
function foo(Collection $dogs)
{
	// Dog expected, Cat given
	$dogs->add(new Cat());
}
```

When implementing a generic interface or extending a generic class, you have two options:

- Preserve the genericness of the parent, the child class will also be generic
- Specify the type variable of the interface/parent class. The child class will not be generic.

Preserving the genericness is done by repeating the same `@template` tags above the child class and passing it to `@extends` and `@implements` tags:

```php
/**
 * @template T
 * @implements Collection<T>
 */
class PersistentCollection implements Collection
{
}
```

If we don’t want our class to be generic, we only use the latter tags:

```php
/**
 * @implements Collection<Dog>
 */
class DogCollection implements Collection
{
}
```

## Covariance & contravariance

<img class="float-none md:float-left md:w-1/2 md:mr-4 mb-2" src="/images/covariance-contravariance.png" alt="Covariance and contravariance" />

There’s one more use case generics solve, but first I need to explain these two terms. Covariance and contravariance describe relationships between related types.

When we describe a type being covariant it means it’s more specific in relation to its parent class or an implemented interface.

A type is contravariant if it’s more general in relation to its child class or an implementation.

All of this is important because languages need to enforce some constraints in parameter types and return types in child classes and interface implementations in order to guarantee type safety.

### Parameter type must be contravariant

Let’s say we have an interface called DogFeeder, and wherever DogFeeder is typehinted, the code is free to pass any Dog to the feed method:

```php
interface DogFeeder
{
	function feed(Dog $dog);
}

function feedChihuahua(DogFeeder $feeder)
{
	$feeder->feed(new Chihuahua()); // this code is OK
}
```

If we implement a BulldogFeeder that narrows the parameter type (it’s covariant, not contravariant!), we have a problem. If we pass the BulldogFeeder into the `feedChihuahua()`function, the code would crash, because `BulldogFeeder::feed()` does not accept a chihuahua:

```php
class BulldogFeeder implements DogFeeder
{
	function feed(Bulldog $dog) { ... }
}

feedChihuahua(new BulldogFeeder()); // 💥
```

Fortunately, PHP does not allow us to do this. But since we’re still writing a lot of types in PHPDocs only, static analysis has to check for these errors.

On the other hand, if we implement DogFeeder with a more general type than a Dog, let’s say an Animal, we’re fine:

```php
class AnimalFeeder implements DogFeeder
{
	public function feed(Animal $animal) { ... }
}
```

This class accepts all dogs, and on top of that all animals as well. Animal is contravariant to Dog.

### Return type must be covariant

With return types it’s a different story. Return types can be more specific in child classes. Let’s say we have an interface called DogShelter:

```php
interface DogShelter
{
	function get(): Dog;
}
```

When a class implements this interface, we have to make sure that whatever it returns, it can still `bark()`. It would be wrong to return something less specific, like an Animal, but it’s fine to return a Chihuahua.

### These rules are useful, but sometimes limiting

Sometimes I’m tempted to have a covariant parameter type even if it’s forbidden. Let’s say we have a Consumer interface for consuming RabbitMQ messages:

```php
interface Consumer
{
	function consume(Message $message);
}
```

When we’re implementing the interface to consume a specific message type, we’re tempted to specify it in the parameter type:

```php
class SendMailMessageConsumer implements Consumer
{
	function consume(SendMailMessage $message) { ... }
}
```

Which isn’t valid because the type isn’t contravariant. But we **know** that this consumer will not be called with any other message type thanks to how we’ve implemented our infrastructure code.

What can we do about this?

One option is to comment out the method in the interface and ignore the fact that we’d be calling an undefined method:

```php
interface Consumer
{
	// function consume(Message $message);
}
```

But that’s dangerous territory.

There’s a better and completely type-safe way thanks to generics. We have to make the Consumer interface generic and the parameter type should be influenced by the type variable:

```php
/**
 * @template T of Message
 */
interface Consumer
{
  /**
   * @param T $message
   */
  function consume(Message $message);
}
```

The consumer implementation specifies the message type using the `@implements` tag:

```php
/**
 * @implements Consumer<SendMailMessage>
 */
class SendMailMessageConsumer implements Consumer
{
	function consume(Message $message) { ... }
}
```

We can choose to omit the method PHPDoc and PHPStan will still know that `$message` can only be `SendMailMessage`. It will also check all calls to SendMailMessageConsumer to report whether only `SendMailMessage`type is passed into the method.

If you use an IDE and want to take advantage of autocompletion, you can add `@param SendMailMessage $message` above the method.

This way is totally type-safe. PHPStan will report any violations that don’t adhere to the type system. Even Barbara Liskov is [happy with it](https://en.wikipedia.org/wiki/Liskov_substitution_principle).

## IDE compatibility

Unfortunately the current generation IDEs do not understand `@template` and related tags. You can choose to use type variables only inside `@phpstan-`prefixed tags, and leave non-prefixed tags with types that IDEs and other tools understand today:

```php
/**
 * @phpstan-template T of \Exception
 *
 * @param \Exception $param
 * @return \Exception
 *
 * @phpstan-param T $param
 * @phpstan-return T
 */
function foo($param) { ... }
```

## Type-safe iterators and generators

Some built-in PHP classes are generic in their nature. To safely use an Iterator, you should specify what keys and values it contains. All of these examples can be used as types in phpDocs:

```
iterable<Value>
iterable<Key, Value>
Traversable<Value>
Traversable<Key, Value>
Iterator<Value>
Iterator<Key, Value>
IteratorAggregate<Value>
IteratorAggregate<Key, Value>
```

[Generator](https://www.php.net/manual/en/language.generators.overview.php) is a complex PHP feature. Besides iterating over the generator and getting its keys and values, you can also send values back to it and even use the `return` keyword besides `yield` in the same method body. That’s why it needs a more complex generic signature:

```
Generator<TKey, TValue, TSend, TReturn>
```

And PHPStan can type-check all of that. Try it out in the [on-line PHPStan playground](https://phpstan.org/r/0eaa8d12-2237-489a-9165-eb243d637329):

```php
class Foo {}
class Bar {}

/**
 * @return \Generator<int, string, Foo, Bar>
 */
function foo(): \Generator
{
	yield 'foo' => new Foo(); // wrong key and value
	$send = yield 1 => 'foo'; // correct
	// $send is Foo

	if (rand(0, 1)) {
		return $send; // wrong
	}

	return new Bar(); // correct
}

$generator = foo();
$generator->send(1); // wrong, expects Foo
$generator->getReturn(); // Bar
```

## Your turn!

Now that you understand what generics are for, it’s up to you to come up with possible uses inside the codebases you work with. They allow you to describe more specific types coming to and from functions and methods. So anywhere you currently use `mixed` and `object` but could take advantage of more precise types, generics could come in handy. They bring type safety to otherwise unapproachable places.

PHPStan 0.12 with generics support (and much much more!) is coming out this week.
