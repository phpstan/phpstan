---
title: PHPDocs Basics
---

PHPDocs are a big part of what makes PHPStan work. PHP in its most recent versions can express a lot of things in the native typehints, but it still leaves a lot of room for PHPDocs to augment the information.

Valid PHPDocs start with `/**`. Variants starting only with `/*` or line comments `//` are not considered PHPDocs. [^phpdocs]

[^phpdocs]: Only the `/**` style comments are supported because they're represented [with different tokens](https://www.php.net/manual/en/tokens.php) (`T_DOC_COMMENT`) by the PHP parser and only this token type is supposed to represent a PHPDoc.

[Learn more about PHPDoc types »](/writing-php-code/phpdoc-types) you can use in the tags described below.

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
	
Would you like to fix incorrect PHPDocs in 3<sup>rd</sup> party code you have in /vendor? You can! Check out [Stub Files »](/user-guide/stub-files)

</div>

Methods and functions
----------

This is how a valid PHPDoc above a function or a method can look like:

```php
/**
 * @param Foo $param
 * @return Bar
 */
function foo($param) { ... }
```

Properties
----------

PHPDocs can be written above class properties to denote their type:

```php
/**
 * @var Foo
 */
private $bar;
```

Inline @var
------------

Casting a type using an inline `@var` PHPDocs should be used only as a last resort. It can be used in a variable assignment like this:

```php
/** @var Foo $foo */
$foo = createFoo();
```

This is usually done if the symbol on the right side of the `=` operator has a wrong type, or isn't specific enough. Usage of inline `@var` is problematic for a couple of reasons:

1) Because it might be used to correct wrong type information of the called symbol, PHPStan always trusts it. But if there's a mistake in this annotation, the analysis of the code below might be wrong.
2) The inline `@var` needs to be repeated above all usages of the symbol which leads to repetition in the codebase.

Instead, the type should be fixed at its source. If the called symbol comes from 3rd party code, you can correct it using a [stub file](/user-guide/stub-files). If the return type differs in each call based on the passed arguments, you can use [generics](/blog/generics-in-php-using-phpdocs), or write a [dynamic return type extension](/developing-extensions/dynamic-return-type-extensions) instead.

If you really need to use an inline `@var`, consider an alternative - an `assert()` call, which can throw an exception at runtime, so the code execution doesn't continue if the requirements aren't met.

```php
$foo = createFoo();
assert($foo instanceof Foo);
```

Magic properties
-------------

For custom `__get`/`__set` methods logic, a `@property` PHPDoc tag can be placed above a class. If the property is only supposed to be read or written to, `@property-read`/`@property-write` variants can be used.

```php
/**
 * @property int $foo
 * @property-read string $bar
 * @property-write \stdClass $baz
 */
class Foo { ... }
```

The `@property` tag can also be used to override wrong property type from a parent class.

<!-- TODO link Solving undefined properties -->

Magic methods
-------------

For custom `__call` methods logic, a `@method` PHPDoc tag can be placed above a class:

```php
/**
 * @method int computeSum(int $a, int $b)
 * @method void doSomething()
 * @method static int staticMethod()
 * @method int doMagic(int $a, int $b = 123)
 */
class Foo { ... }
```

Exceptions
-------------

Functions and methods can be marked as throwing an exception with `@throws`:

```php
/**
 * @throws \InvalidArgumentException
 */
function doFoo(): void
{
    // ...
}
```

This is useful for [precise analysis of try-catch-finally](/blog/precise-try-catch-finally-analysis) blocks, and also for bringing exceptions under control by enforcing [documentation and handling of checked exceptions](/blog/bring-your-exceptions-under-control).


Callables
-------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.11.0</div>

Aside from describing [callable signatures in PHPDoc types](/writing-php-code/phpdoc-types#callables), PHPStan also supports declaring whether the callable is executed immediately or saved for later when passed into a function or a method.

By default PHPStan considers callables passed into function calls to be executed immediately, and callables passed into object method calls to be executed later. These defaults can be overridden with `@param-immediately-invoked-callable` and `@param-later-invoked-callable` PHPDoc tags:

```php
/**
 * @param-later-invoked-callable $cb
 */
function acceptCallableAndCallLater(callable $cb): void
{
    // ...
}

class Foo
{
	/**
	 * @param-immediately-invoked-callable $cb
	 */
	public function acceptAndCallCallableNow(callable $cb): void
	{

	}
}
```

When passing a closure (an anonymous function or an arrow function) into a function/method that binds the closure to a different object, changing the meaning of `$this` inside the closure, you can use `@param-closure-this` PHPDoc tag to declare what `$this` is going to be when this closure is called:

```php
/**
 * @param-closure-this Bar $cb
 */
function doFoo(Closure $cb)
{
	$cb->bindTo(new Bar());
	// ...
}

doFoo(function () {
	// $this is Bar
});
```

Mixins
-------------

When a class delegates unknown method calls and property accesses to a different class using `__call` and `__get`/`__set`, we can describe the relationship using `@mixin` PHPDoc tag:

```php
class A
{
    public function doA(): void
    {
    }
}

/**
 * @mixin A
 */
class B
{
    public function doB(): void
    {
    }

    public function __call($name, $arguments)
    {
        (new A())->$name(...$arguments);
    }
}

$b = new B();
$b->doB();
$b->doA(); // works
```

It also works with [generics](/blog/generics-in-php-using-phpdocs):

```php
/**
 * @template T
 * @mixin T
 */
class Delegatee
{

    /** @var T */
    private $delegate;

    /**
     * @param T $delegate
     */
    public function __construct($delegate)
    {
        $this->delegate = $delegate;
    }

    public function __call($name, $arguments)
    {
        return $this->delegate->$name(...$arguments);
    }

}

$d = new Delegatee(new \Exception('My message'));
echo $d->getMessage(); // PHPStan knows the method is on Exception
```

Combining PHPDoc types with native typehints
-------------

PHPDocs can also complement native typehints with additional information. The most common use-case is telling PHPStan what's in an array:

```php
/**
 * @param User[] $users
 */
function foo(array $users) { ... }
```

You can also use an alternative `array<User>` syntax, or even specify the key type:

```php
/**
 * @param array<int, User> $users
 */
function foo(array $users) { ... }
```

More about this in [PHPDoc Types > Iterables](/writing-php-code/phpdoc-types#iterables).

Using `@return static` along with the `self` native typehint means that the method returns a child class ([see example](/r/5f856517-5303-4237-95de-2bfa5bc4b9de)):

```php
/**
 * @return static
 */
public function returnStatic(): self
{
    return $this;
}
```

A narrower `@return $this` instead of `@return static` can also be used, and PHPStan will check if you're really returning the same object instance and not just the child class.

Variadic functions
-------------------------

This allows specifying functions or methods which have a variable amount of parameters (available since [PHP 5.6](https://www.php.net/manual/en/migration56.new-features.php)).

Your code can look like this:

```php
/**
 * @param string $arg
 * @param string ...$additional
 */
function foo($arg, ...$additional)
{

}
```

Generics
---------------

PHPDoc tags `@template`, `@template-covariant`, `@template-contravariant`, `@extends`, `@implements`, and `@use` are reserved for generics. Learn more about [generics »](/blog/generics-in-php-using-phpdocs), [covariance »](/blog/whats-up-with-template-covariant), [contravariance »](https://jiripudil.cz/blog/contravariant-template-types), and [type projections »](/blog/guide-to-call-site-generic-variance). Also check out [Generics By Examples »](/blog/generics-by-examples).

Narrowing types after function call
----------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.9.0</div>

PHPDoc tags `@phpstan-assert`, `@phpstan-assert-if-true`, `@phpstan-assert-if-false` are used to inform PHPStan about type-narrowing happening inside called functions and methods. [Learn more »](/writing-php-code/narrowing-types#custom-type-checking-functions-and-methods)

Setting parameter type passed by reference
---------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.9.0</div>

PHPDoc tag `@param-out` can be used to set a parameter type passed by reference:

```php
/**
 * @param-out int $i
 */
function foo(mixed &$i): void
{
    $i = 5;
}

foo($a);
\PHPStan\dumpType($a); // int
```

Change type of current object after calling a method
---------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.9.0</div>

PHPDoc tags `@phpstan-self-out` or `@phpstan-this-out` can be used to change the type of the current object after calling a method on it. This is useful for generic mutable objects.

```php
/**
 * @template TValue
 */
class Collection
{
	
	// ...
	
	/**
	 * @template TItemValue
	 * @param TItemValue $item
	 * @phpstan-self-out self<TValue|TItemValue>
	 */
	public function add($item): void
	{
		// ...
	}
	
}

/** @param Collection<int> $c */
function foo(Collection $c, string $s): void
{
	$c->add($s);
	\PHPStan\dumpType($c); // Collection<int|string>
}
```

Deprecations
---------------

Use `@deprecated` tag to mark declarations as deprecated:

```php
/** @deprecated Optional description */
class Foo
{
}
```

Install [`phpstan-deprecation-rules`](https://github.com/phpstan/phpstan-deprecation-rules) extension to have usages of deprecated symbols reported.

The `@deprecated` PHPDoc tag is inherited to implicitly mark overridden methods in child classes also as deprecated:

```php
class Foo
{
	/** @deprecated */
	public function doFoo(): void
	{
		// ...
	}
}

class Bar extends Foo
{
	public function doFoo(): void
	{
		// ...
	}
}

$bar = new Bar();
$bar->doFoo(); // Call to deprecated method doFoo() of class Bar.
```

To break the inheritance chain and un-mark `Bar::doFoo()` as deprecated, use `@not-deprecated` PHPDoc tag:

```php
class Bar extends Foo
{
	/** @not-deprecated */
	public function doFoo(): void
	{
		// ...
	}
}

$bar = new Bar();
$bar->doFoo(); // OK
```

Impure functions
---------------

By default, [PHPStan considers all functions that return a value to be pure](https://phpstan.org/blog/remembering-and-forgetting-returned-values). That means that a second call to the same function in the same scope will return the same narrowed type. If you have a function that may return different values on successive calls based on a global state like a random number generator, database, or time, then the function is impure. You can tell PHPStan about impure functions and methods with the `@phpstan-impure` tag:

```php
/** @phpstan-impure */
function impureFunction(): bool
{
    return rand(0, 1) === 0 ? true : false;
}
```

The `@phpstan-pure` tag is also available should you need it, for example if you've set `rememberPossiblyImpureFunctionValues: false` in your configuration file (available in PHPStan 1.8.0). See [Config Reference](https://phpstan.org/config-reference#rememberpossiblyimpurefunctionvalues) for more details.


Enforcing class inheritance for interfaces and traits
---------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.10.56</div>

PHPDoc tag `@phpstan-require-extends` can be put above interfaces and traits. When this interface is implemented or trait is used by a class, this class has to extend a parent declared by this tag.

```php
class Bar
{
}

/**
 * @phpstan-require-extends Bar
 */
interface Foo
{
}

// Error: Interface Foo requires implementing class to extend Bar, but Baz does not.
class Baz implements Foo
{
}

// OK
class Lorem extends Bar  implements Foo
{
}
```

This is useful for solving "Access to an undefined property" error when trying to access a property declared by PHPDoc tag `@property` on an interface on PHP 8.2+. [Learn more »](/blog/solving-phpstan-access-to-undefined-property#making-%40property-phpdoc-above-interfaces-work-on-php-8.2%2B)


Enforcing implementing an interface for traits
---------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.10.56</div>

PHPDoc tag `@phpstan-require-implements` can be put above traits. When this trait is used by a class, this class has to implement an interface declared by this tag.

```php
interface Bar
{
}

/**
 * @phpstan-require-implements Bar
 */
trait Foo
{
}

// Error: Trait Foo requires using class to implement Bar, but Baz does not.
class Baz
{
	use Foo;
}

// OK
class Lorem implements Bar
{
	use Foo;
}
```

Prefixed tags
---------------

Supported tags (`@var`, `@param`, `@return`, and all [generics-related ones](/blog/generics-in-php-using-phpdocs)) can be prefixed with `@phpstan-`:

```php
/**
 * @phpstan-param Foo $param
 * @phpstan-return Bar
 */
function foo ($param) { ... }
```

This is useful in the context of advanced types and [generics](/blog/generics-in-php-using-phpdocs). IDEs and other PHP tools might not understand the advanced types that PHPStan takes advantage of. So you can leave the ordinary `@param` in the PHPDoc and add a `@phpstan-param` with an advanced type syntax.

Classes named after internal PHP types
-----------------------------------------------------------

When having classes named like `Resource`, `Double`, `Number` (or `Mixed` until PHP 8), there is no possible way to distinguish between either the PHP internal type or the custom class to use in the PHPDoc. By default, PHPStan will consider the type as being the PHP internal type, which means some false-positives can appear.

```php
/**
 * @param Resource $var
 */
public function foo(Resource $var): void { ... }
```

To make PHPStan understand the passed argument must be an instance of a `Resource` object, use a fully-qualified name in the PHPDoc. PHPStan will understand that as the object of `My\Resource` class.

```php
/**
 * @param \My\Resource $var
 */
public function foo(Resource $var): void { ... }
```

Readonly properties
-------------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.7.0</div>

PHPStan supports native PHP 8.1 [readonly properties](https://wiki.php.net/rfc/readonly_properties_v2) and validates their correct usage. For older PHP versions, PHPStan also understands the `@readonly` PHPDoc tag to apply similar rules.

```php
class Foo
{
	/** @readonly */
	public string $bar;
}

(new Foo())->bar = 'baz'; // @readonly property Foo::$bar is assigned outside of its declaring class.
```

This feature needs to be enabled via feature toggle by opting in to [bleeding edge](https://phpstan.org/blog/what-is-bleeding-edge).

Immutable /readonly classes
-----------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.7.0</div>

`@immutable` or `@readonly` on the class can be used to make PHPStan treat every property of that class as being readonly.

```php
/** @immutable */
class Foo
{
	public string $bar;
}

(new Foo())->bar = 'baz'; // @readonly property Foo::$bar is assigned outside of its declaring class.
```

This feature needs to be enabled via feature toggle by opting in to [bleeding edge](https://phpstan.org/blog/what-is-bleeding-edge).
