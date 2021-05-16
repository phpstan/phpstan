---
title: "How PHPStan analyses traits"
date: 2021-05-16
tags: guides
---

Traits are a mechanism to reuse code. They're copy-paste implemented at the language level. Contents of a used trait become part of the class that uses it.

Consider the following code:

```php
trait FooTrait
{

	public function doFoo(): int
	{
		// Access to an undefined property FooTrait::$foo.
		// Or is it?
		return $this->foo;
	}

}
```

If PHPStan would approach trait analysis in a traditional way, it would report the error. But whether the error is real or not depends on the class that uses the trait:

```php
class Foo
{

	// there's no property $this–>foo, error should be reported
	use FooTrait;

}

class Bar
{

	// the property exists, do not report the error
	use FooTrait;

	private int $foo;

}
```

PHPStan does not analyse traits in isolation, it analyses them only as part of a class that uses them. In order for a trait to be analysed, both the using class and the trait need to be in analysed paths. Traits that are used zero times will not be analysed at all. Trait that is used 10 times will be analysed 10 times.

Let's say we have file `Foo.php` that contains class `Foo` and then `FooTrait.php` that contains trait `FooTrait` that's used by class `Foo`.

* If we run `vendor/bin/phpstan analyse Foo.php`, the trait will not be analysed.
* If we run `vendor/bin/phpstan analyse FooTrait.php`, the trait will not be analysed.
* If we run `vendor/bin/phpstan analyse Foo.php FooTrait.php`, the trait will be analysed.

This is also one of the reasons [why you should always analyse your whole project](/blog/why-you-should-always-analyse-whole-project).
