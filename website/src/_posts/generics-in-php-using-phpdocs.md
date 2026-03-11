---
title: "Generics in PHP using PHPDocs"
date: 2019-12-02
tags: guides
---

Generics is a really complex topic. It's basically a programming language inside a programming language. But it's also incredibly useful. I hope to introduce it pretty easily for you with a lot of examples so you can get a good idea of how generics work and how you can write your own generic code.

I gave a talk about this topic at IPC Munich 2023:

<iframe
	src="https://www.youtube.com/embed/eRvH9CP4YfY"
	frameborder="0"
	allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
	allowfullscreen
	class="w-full aspect-video mb-8"
></iframe>

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">

Also check out practical examples in the [Generics By Examples](/blog/generics-by-examples) article.

</div>

## Why generics?

PHP itself doesn't support generics natively. There was [an RFC](https://wiki.php.net/rfc/generics) back in 2016 that didn't go anywhere because it's a really complicated thing to implement in the language runtime. If you want to learn more about the state of native generics in PHP, check out the PHP Foundation's articles on [the state of generics and collections](https://thephp.foundation/blog/2024/08/19/state-of-generics-and-collections/) and [compile-time generics](https://thephp.foundation/blog/2025/08/05/compile-generics/).

If real generics were in PHP, they'd look something like this using native syntax:

```php
class Entry<KeyType, ValueType>
{
    // ...
}

$entry = new Entry<int, string>(1, 'test');
```

But this isn't possible. The best we can do today is to use PHPDocs. And that's where PHPStan comes in.

Without generics, if your function returns different types based on the argument types, you'd have to resort to returning a general type like `object` or `mixed`:

```php
function findEntity(string $className, int $id): ?object
{
	// finds and returns an entity object based on $className
	// and primary key in $id
}
```

This produces an unnecessarily wide return type. If you don't use generics, you'll probably find yourself getting errors like:

> Call to an undefined method object::doSomething().

or:

> Cannot access property $title on mixed.

Because you can't safely call methods or access properties on `object` or `mixed` — you need a specific class name. On the other hand, if you use generics, your code will be more type-safe. Generics will make sure you have fewer false positives, and at the same time PHPStan will report more correct errors for you. It's all about making types during static analysis more specific.

## Infinite number of signatures

The simplest explanation I came up with is that generics allow you to create an infinite number of signatures for your function. Imagine a function that returns the same type it receives — for integer it returns an integer, for string it returns a string. Without generics, we'd probably type it as accepting and returning `mixed`. But with generics, we can describe the exact relationship:

```php
/**
 * @param int $param
 * @return int
 */
function foo($param)
{
	...
}
```

These types are set in stone. And that's what generics are for. They offer generating an infinite number of signatures for functions and methods based on rules developers can define themselves.

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

You introduce a type variable using `@template` and then you can use that identifier in your `@param` and `@return` tags. The type variable name can be anything — the convention is usually just a single uppercase letter, but it can be any identifier as long as you don't use an existing class name.

How does this work in practice? For each call of a generic function, PHPStan looks at the parameters, tries to infer what the type variables are from the argument types, and then applies that knowledge to the return type.

You can also limit which types can be used in place of the type variable with an upper bound using the `of` keyword. This is called a "type variable bound":

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

### Default type

Type variables can also have a default type using `=`. When type arguments have defaults, they don't have to be specified when using the generic class:

```php
/**
 * @template T = string
 * @template U = int
 */
class Pair {}
```

With the class above, these are all valid:

- `Pair` — both `T` and `U` use their defaults (`string` and `int`)
- `Pair<bool>` — `T` is `bool`, `U` uses its default (`int`)
- `Pair<bool, float>` — both `T` and `U` are specified explicitly

Defaults can be combined with upper bounds: `@template T of object = stdClass`.

In some situations the `@template-covariant` PHPDoc tag can be used instead. [Check out the guide on `@template-covariant` »](/blog/whats-up-with-template-covariant).

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

If you then call `findEntity(Article::class, 1)`, PHPStan will know that you're getting an Article object or null!

You can combine it however you want. Marking the return type as `T[]` (for example for a `findAll()` function) would infer the return type as an array of Articles:

```php
/**
 * @template T
 * @param class-string<T> $className
 * @return T[]
 */
function findAll(string $className)
{
	// ...
}
```

Generics can get pretty complicated. Here's a little quiz — can you guess what built-in PHP function this signature describes?

```php
/**
 * @template K
 * @template V
 * @template V2
 * @param callable(V): V2 $callback
 * @param array<K, V> $array
 * @return array<K, V2>
 */
function ???($callback, $array) {}
```

It's `array_map`! We need three type variables: `K` for the array key, `V` for the input value, and `V2` for the output value. The callable transforms `V` to `V2`, and the function returns an array with the same keys but the new value type. PHPStan uses this signature to check that the callback you pass in actually matches the array value type, and it correctly infers the return type.

## Class-level generics

Up until this point, I've written only about function-level or method-level generics. But a whole other area is type variables above classes. We can also make our classes generic by putting `@template` above a class or an interface:

```php
/**
 * @template T
 */
interface Collection
{
}
```

Once a class has one or more template type variables, we call that class generic. We can then reference the type variable identifier in our methods and properties:

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

The order matters — the first `@template` tag corresponds to the first position in the angle brackets, and so on for multiple templates.

The types of the Collection can be specified when you're typehinting it somewhere else:

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

- Specify the type variable of the interface/parent class. The child class will not be generic.
- Preserve the genericness of the parent, the child class will also be generic.

Specifying the type variable is done with `@extends`, `@implements`, and `@use` tags — they match the corresponding PHP keywords:

```php
/**
 * @implements Collection<Dog>
 */
class DogCollection implements Collection
{
}
```

Preserving the genericness is done by repeating the same `@template` tags above the child class and passing them through:

```php
/**
 * @template T
 * @implements Collection<T>
 */
class PersistentCollection implements Collection
{
}
```

The same works for `@extends` when extending a class, and `@use` when using a trait:

```php
/** @template T */
trait FooTrait { ... }

class Foo
{
	/** @use FooTrait<Bar> */
	use FooTrait;
}
```

## "Does not specify its types" error

One thing that people usually have problems with is that they try to use generic classes and PHPStan yells at them:

```php
class HelloWorld
{
	function sayHello(ReflectionClass $r): void
	{
	}
}
```

> Method HelloWorld::sayHello() has parameter $r with generic class ReflectionClass but does not specify its types: T

What does PHPStan want you to do? It wants you to add a PHPDoc that specifies what `T` is. `ReflectionClass` is a built-in PHP class, but PHPStan uses [stub files](https://github.com/phpstan/phpstan-src/tree/2.2.x/stubs) to make some built-in classes generic because it's really useful.

This is how the declaration of `ReflectionClass` looks like in PHPStan:

```php
/**
 * @template T of object
 */
class ReflectionClass { ... }
```

You have several options how to fix the error:

If you just don't care about generics in this situation and want to put something there, you can use the same thing that the template has as a bound. If the bound is `object`, use `object`. If the bound is missing, use `mixed`:

```php
/** @param ReflectionClass<object> $r */
function sayHello(ReflectionClass $r): void
{
}
```

Or you can say you accept reflections of only a certain class:

```php
/** @param ReflectionClass<Foo> $r */
function sayHello(ReflectionClass $r): void
{
}
```

Or maybe the most powerful option — you can make the function itself generic:

```php
/**
 * @template T of object
 * @param ReflectionClass<T> $r
 * @return T
 */
function sayHello(ReflectionClass $r): object
{
	return $r->newInstance();
}
```

Now when you pass in a specific `ReflectionClass` when calling this method, PHPStan will extract what `T` is and use it as the return type.

The same applies when implementing generic interfaces. For example:

```php
class Foo implements IteratorAggregate
{
}
```

> Class Foo implements generic interface IteratorAggregate but does not specify its types: TKey, TValue

You need to use `@implements` to tell PHPStan what the iterated keys and values are:

```php
/** @implements IteratorAggregate<int, Bar> */
class Foo implements IteratorAggregate
{
}
```

This tells PHPStan what happens when you put an object of class `Foo` into a `foreach` — the iterated keys are going to be integers and the iterated values are going to be objects of class `Bar`.

## Built-in generic PHP classes

PHPStan makes a lot of built-in PHP classes generic through [stub files](https://github.com/phpstan/phpstan-src/tree/2.2.x/stubs). You can inspect how the stubs look like — for example, here's the stub for `ArrayIterator`:

```php
/**
 * @template TKey of array-key
 * @template TValue
 * @implements SeekableIterator<TKey, TValue>
 * @implements ArrayAccess<TKey, TValue>
 */
class ArrayIterator implements SeekableIterator, ArrayAccess, Countable
{
	/**
	 * @param array<TKey, TValue> $array
	 * @param int $flags
	 */
	public function __construct($array = array(), $flags = 0) { }

	/**
	 * @param TValue $value
	 * @return void
	 */
	public function append($value) { }

	// ...
}
```

The method bodies are empty because PHPStan is only interested in the PHPDocs — it uses them instead of the original PHP docs of these classes.

You can also use stub files yourself to override or improve wrong third-party PHPDocs, or to make a third-party class generic that the creator of the dependency doesn't know anything about. Learn more in the [stub files documentation](/user-guide/stub-files).

## Covariance & contravariance

<img class="float-none md:float-left md:w-1/2 md:mr-4 mb-2" src="/images/covariance-contravariance.png" alt="Covariance and contravariance" />

There's one more use case generics solve, but first I need to explain these two terms. Covariance and contravariance are terms describing relationships between types.

When we're going from a less specific type to a more specific type, it's **covariant**. When we're going from a more specific type to a less specific type, it's **contravariant**. And when we're talking about the same type, it's **invariant**.

All of this is important because languages need to enforce some constraints in parameter types and return types in child classes and interface implementations in order to guarantee type safety.

### Parameter type must be contravariant

Let's say we have an interface called DogFeeder, and wherever DogFeeder is typehinted, the code is free to pass any Dog to the feed method:

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

If we implement a BulldogFeeder that narrows the parameter type (it's covariant, not contravariant!), we have a problem. If we pass the BulldogFeeder into the `feedChihuahua()`function, the code would crash, because `BulldogFeeder::feed()` does not accept a chihuahua:

```php
class BulldogFeeder implements DogFeeder
{
	function feed(Bulldog $dog) { ... }
}

feedChihuahua(new BulldogFeeder()); // 💥
```

Fortunately, PHP does not allow us to do this. But since we're still writing a lot of types in PHPDocs only, static analysis has to check for these errors.

On the other hand, if we implement DogFeeder with a more general type than a Dog, let's say an Animal, we're fine:

```php
class AnimalFeeder implements DogFeeder
{
	public function feed(Animal $animal) { ... }
}
```

This class accepts all dogs, and on top of that all animals as well. Animal is contravariant to Dog.

### Return type must be covariant

With return types it's a different story. Return types can be more specific in child classes. Let's say we have an interface called DogShelter:

```php
interface DogShelter
{
	function get(): Dog;
}
```

When a class implements this interface, we have to make sure that whatever it returns, it can still `bark()`. It would be wrong to return something less specific, like an Animal, but it's fine to return a Chihuahua.

### These rules are useful, but sometimes limiting

Sometimes I'm tempted to have a covariant parameter type even if it's forbidden. Let's say we have a Consumer interface for consuming RabbitMQ messages:

```php
interface Consumer
{
	function consume(Message $message);
}
```

When we're implementing the interface to consume a specific message type, we're tempted to specify it in the parameter type:

```php
class SendMailMessageConsumer implements Consumer
{
	function consume(SendMailMessage $message) { ... }
}
```

Which isn't valid because you can't narrow parameter types — you can only make them wider. But we **know** that this consumer will not be called with any other message type thanks to how we've implemented our infrastructure code.

What can we do about this?

One option is to comment out the method in the interface and ignore the fact that we'd be calling an undefined method:

```php
interface Consumer
{
	// function consume(Message $message);
}
```

But that's dangerous territory.

There's a better and completely type-safe way thanks to generics. We have to make the Consumer interface generic and the parameter type should be influenced by the type variable:

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

We can choose to omit the method PHPDoc and PHPStan will still know that `$message` can only be `SendMailMessage`. It will also check all calls to SendMailMessageConsumer to report whether only `SendMailMessage` type is passed into the method.

This way is totally type-safe. PHPStan will report any violations that don't adhere to the type system. Even Barbara Liskov is [happy with it](https://en.wikipedia.org/wiki/Liskov_substitution_principle).

## Invariance in generics

You might be surprised that when you have a function that accepts `Collection<Animal>`, you can't pass `Collection<Dog>` there even though `Dog` is an `Animal`:

> Parameter #1 $animals of function foo expects `Collection<Animal>`, `Collection<Dog>` given.

This looks like a bug, right? But in generics you can't really do this. The function that accepts a collection of animals might actually put a cat there:

```php
/** @param Collection<Animal> $animals */
function foo(Collection $animals): void
{
	$animals->add(new Cat()); // valid, no harm done
}
```

This code is harmless and no error is reported. But if we allowed `Collection<Dog>` there, it'd no longer be a collection of dogs — there'd be a cat among them after this call.

By default, `@template` is invariant: `Collection<Animal>` only accepts another `Collection<Animal>`. To learn about `@template-covariant` which lifts this restriction (with a different trade-off), read the [guide on `@template-covariant`](/blog/whats-up-with-template-covariant). And for the most flexible solution, read about [call-site variance](/blog/guide-to-call-site-generic-variance) which lets you decide the variance at the place where you use the type.

## `@phpstan-` prefixed tags

If you use a tool that doesn't understand `@template` and related tags, you can choose to use type variables only inside `@phpstan-`prefixed tags, and leave non-prefixed tags with types that the tool understands:

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

[Generator](https://www.php.net/manual/en/language.generators.overview.php) is a complex PHP feature. Besides iterating over the generator and getting its keys and values, you can also send values back to it and even use the `return` keyword besides `yield` in the same method body. That's why it needs a more complex generic signature:

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

Now that you understand what generics are for, it's up to you to come up with possible uses inside the codebases you work with. They allow you to describe more specific types coming to and from functions and methods. So anywhere you currently use `mixed` and `object` but could take advantage of more precise types, generics could come in handy. They bring type safety to otherwise unapproachable places.

You can read all about the advanced topics in these articles:

- [Generics By Examples](/blog/generics-by-examples)
- [What's Up With @template-covariant?](/blog/whats-up-with-template-covariant)
- [A guide to call-site generic variance](/blog/guide-to-call-site-generic-variance)

---

Do you like PHPStan and use it every day? [**Consider supporting further development of PHPStan on GitHub Sponsors**](https://github.com/sponsors/ondrejmirtes/). I'd really appreciate it!
