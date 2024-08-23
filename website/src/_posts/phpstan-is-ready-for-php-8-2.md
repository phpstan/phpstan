---
title: "PHPStan is ready for PHP 8.2!"
date: 2022-12-08
tags: releases
---

Today is the day of [PHP 8.2](https://www.php.net/releases/8.2/en.php) becoming generally available! It's a pretty quiet year for adjustments needed in PHPStan which is a welcome change after [making sure it fully works with PHP 8.1](/blog/plan-to-support-php-8-1) for about six months up until the end of April of this year.

PHPStan has been ready for PHP 8.2 for over two months already. Last additions [were included in PHPStan 1.8.7](https://github.com/phpstan/phpstan/releases/tag/1.8.7) on October 4th.

As per usual I'm gonna go through some of the interesting changes related to PHP 8.2 and dive into detail what they mean for our static analysis engine.

Dynamic properties deprecated
---------------

I believe [this RFC](https://wiki.php.net/rfc/deprecate_dynamic_properties) moves the language in the right direction, because when you access a property that isn't declared on a class, you can't be sure it really exists and that your code is going to work. This is how PHPStan treats analysed code since the very beginning.

Since PHP 8.2 the following code is marked as deprecated:

```php
class Foo
{

}

$foo = new Foo();
$foo->test = 'oh my';
echo $foo->test;
```

[PHPStan always reported](https://phpstan.org/r/33d1cb67-39ca-40ee-889d-841bc1719123) this code as being wrong, but you were able to work around it with `isset()` and other similar techniques:

```php
function (Foo $foo): void {
    if (isset($foo->test)) {
        // ...
    }
};
```

But with PHP 8.2 and onward, PHPStan knows that this `isset()` [is always going to report `false`](https://phpstan.org/r/05e3273f-500a-4129-9517-4991931e459e).

There are some internal changes in PHPStan to make sure it behaves consistently. For example magic `@property` PHPDocs are not going to have any effect in classes that cannot have dynamic properties.

Disjunctive Normal Form Types
---------------

This [mysteriously-sounding RFC](https://wiki.php.net/rfc/dnf_types) makes it possible to use native intersection types from PHP 8.1 in more scenarios. They can now be used in union types, which for example means they can become nullable: `(A&B)|null`.

Full credit here goes to [Jarda Hanslík](https://twitter.com/kukulich) who [implemented this](https://github.com/Roave/BetterReflection/pull/1198) in Roave/BetterReflection, the library PHPStan relies on for all its reflection needs. Talking of BetterReflection, he actually implemented [all the changes needed there](https://github.com/Roave/BetterReflection/pulls?q=is%3Apr+is%3Aclosed+%22php+8.2%22+author%3Akukulich) for PHP 8.2 to be fully supported, which is awesome!

Updated function signatures
---------------

PHPStan uses several sources about internal PHP functions and methods. There's [functionMap.php](https://github.com/phpstan/phpstan-src/blob/1.12.x/resources/functionMap.php), there's [jetbrains/phpstorm-stubs](https://github.com/jetbrains/phpstorm-stubs), and there's [phpstan/php-8-stubs](https://github.com/phpstan/php-8-stubs).

The last one is extracted from official stubs provided by [php-src](https://github.com/php/php-src/). And because these change from version to version, there has to be a way to express those differences.

[Such stubs](https://github.com/phpstan/php-8-stubs/blob/45ace6223009aa9275ac8fbafb7f8066de5813c6/stubs/Zend/restore_error_handler.php) now come with special `#[Since]` and `#[Until]` attributes and the definitions are cleaned up before being interpreted by PHPStan:

```php
#[\Until('8.2')]
function restore_error_handler() : bool
{
}
#[\Since('8.2')]
function restore_error_handler() : true
{
}
```

Readonly classes
---------------

`Readonly` classes [mark all their properties](https://php.watch/versions/8.2/readonly-classes) as being `readonly`. PHPStan applies [all the same limitations](https://phpstan.org/r/2e1c02a3-b748-4d76-8689-e3d0efb738e9) to readonly classes it already applies to readonly properties:

```php
readonly class Foo
{

    // Readonly property must have a native type.
    public $foo;

    // Class Foo has an uninitialized readonly property $bar. Assign it in the constructor.
    public int $bar;

    public function setBar(int $bar): void
    {
        // Readonly property Foo::$bar is assigned outside of the constructor.
        $this->bar = $bar;
    }

}

$foo = new Foo();

// Readonly property Foo::$bar is assigned outside of its declaring class.
$foo->bar = $bar;
```

---

Do you like PHPStan and use it every day? [**Consider supporting further development of PHPStan on GitHub Sponsors**](https://github.com/sponsors/ondrejmirtes/). I’d really appreciate it!
