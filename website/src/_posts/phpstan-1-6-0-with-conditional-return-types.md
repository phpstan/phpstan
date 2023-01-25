---
title: "PHPStan 1.6.0 With Conditional Return Types and More!"
date: 2022-04-26
tags: releases
---

[PHPStan 1.6.0](https://github.com/phpstan/phpstan/releases/tag/1.6.0) which has been in development for the past month or so brings a surprising amount of new features and improvements. Let's dive right into them!

Conditional return types
--------------------------

The majority of this feature was developed by [Richard van Velzen](https://github.com/rvanvelzen). {.text-sm}

Since its first release, PHPStan has offered a way to describe functions that return different types based on arguments passed in the call. So-called [dynamic return type extensions](/developing-extensions/dynamic-return-type-extensions) are really flexible - you can resolve the type based on any logic you can implement. But it comes at a cost - there's a learning curve to the [core concepts](/developing-extensions/core-concepts) of writing PHPStan extensions.

In [PHPStan 0.12](/blog/phpstan-0-12-released) came [generics](/blog/generics-in-php-using-phpdocs). They cover a portion of scenarios with a special PHPDoc syntax where dynamic return type extensions with custom logic were previously needed.

Today PHPStan takes another step in the accessibility of these advanced features. You no longer have to be an expert to take advantage of them. Funnily enough, someone might even call these "no-code" solutions 🤣

Conditional return types allow you to write an "if-else" logic in the PHPDoc `@return` tag.

```php
/**
 * @return ($as_float is true ? float : string)
 */
function microtime(bool $as_float): string|float
{
    ...
}
```

Conditional return types can be combined with generics:

```php
/**
 * @template T of object
 * @param class-string<T> $class
 * @return ($throw is true ? T : T|null)
 */
function getService(string $class, bool $throw = true): ?object
{
    ...
}
```

Generic template type can be used inside the condition as well:

```php
/**
 * @template T of int
 * @template U
 * @param T $size
 * @param U $value
 * @return (T is positive-int ? non-empty-array<U> : array<U>)
 */
function fillArray(int $size, $value): array
{
    ...
}
```

More complicated conditions can be expressed by nesting the conditional types:

```php
/**
 * @param int|float $a
 * @param int|float $b
 * @return ($a is int ? ($b is int ? int : float) : float)
 */
function add($a, $b) {
    return $a + $b;
}
```

Integer masks
--------------------------

This feature was developed by [Richard van Velzen](https://github.com/rvanvelzen). {.text-sm}

A common pattern to configure behaviour of a function is to accept an integer value that consists of different flags joined with the `|` operator:

```php
echo json_encode($a, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
```

For this pattern to work, each of these values has to be a distinct power-of-two value (1, 2, 4, 8, ...).

You can now use this in PHPStan:

```php
const FOO = 1;
const BAR = 2;
const BAZ = 4;

/** @param int-mask<FOO, BAR, BAZ> $flag */
function test(int $flag): void
{
    $isFoo = ($flag & FOO) !== 0;
    $isBar = ($flag & BAR) !== 0;
    $isBaz = ($flag & BAZ) !== 0;
}

test(FOO); // OK
test(FOO | BAR); // OK
test(FOO | 8); // Error: Parameter #1 $flag of function test expects int<0, 7>, 9 given.
```

There's also the `int-mask-of<...>` variant which accepts a union type of integer values instead of comma-separated values:

```php
class HelloWorld
{
    const FOO_BAR = 1;
    const FOO_BAZ = 2;

    /** @param int-mask-of<self::FOO_*> $flags */
    public static function sayHello(int $flags): void
    {
        ...
    }
}
```

Lower memory consumption
--------------------------

PHPStan used to be a really hungry beast. To the point of being brutally killed by CI runners because it consumed not just all the memory up to the `memory_limit` in php.ini, but also all the memory assigned to the runner hardware.

Now it's a less hungry beast. How did I make it happen? It's useful to realize what's going on inside a running program. It's fine to consume memory as useful things are being achieved, but in order to consume less memory in total, it has to be freed afterwards so that it can be used again by different data needed when analysing the next file in line.

To debug memory leaks, I use the [`php-meminfo`](https://github.com/BitOne/php-meminfo) extension. It exports all the scalar values and objects held in memory to a JSON file. It also ships with an analyzer that produces various statistics so that you know where you should start with the optimization.

I quickly realized that most of the memory is occupied by [AST](/developing-extensions/abstract-syntax-tree) nodes. PHP frees the memory occupied by an object when there are no more references to it [^refcount]. It didn't work in case of AST nodes because they kept pointing at each other:

[^refcount]: That's called reference counting.

{% mermaid %}
    flowchart LR;
    TryCatch== stmts ==>array
    array== 0 ==>Stmt1
    array== 1 ==>Stmt2
    array== 2 ==>Stmt3
    Stmt1== parent ==>TryCatch
    Stmt2== parent ==>TryCatch
    Stmt3== parent ==>TryCatch
    Stmt1== next ==>Stmt2
    Stmt2== next ==>Stmt3
    Stmt2== prev ==>Stmt1
    Stmt3== prev ==>Stmt2
{% endmermaid %}

Sure, there's also the garbage collector that [collects cycles](https://www.php.net/manual/en/features.gc.collecting-cycles.php) like these. But PHPStan had it [turned off](https://github.com/phpstan/phpstan-src/blob/1f3ecf8512008fb60fea2258ba53f914118d900f/bin/phpstan#L9) because it improved performance time-wise. When there's a lot of cycles, applications that haven't called `gc_disable()` are burning a lot of CPU time.

Because getting rid of `parent`/`previous`/`next` node attributes is a backward compatibility break for custom rules that read them, I do it only when the [Bleeding Edge](/blog/what-is-bleeding-edge) configuration is enabled. I've written [an article on how to make these rules work again](/blog/preprocessing-ast-for-custom-rules) even without those memory-consuming references.

A nice twist in all this is that removing the `gc_disable()` call no longer represents a significant performance penalty here so it's [no longer used](https://github.com/phpstan/phpstan-src/commit/c72277a3a9f549ad9bfeb8c02bc2eb697646c8dc). [^php73]

[^php73]: It's still used on PHP 7.2 because PHP 7.3 brought [significant performance improvements](https://github.com/php/php-src/pull/3165) of its garbage collector.

In my testing PHPStan now consumes around 50–70 % less memory with [Bleeding Edge](/blog/what-is-bleeding-edge) enabled.

You can enable Bleeding Edge by putting this into your `phpstan.neon`:

```neon
includes:
    - vendor/phpstan/phpstan/conf/bleedingEdge.neon
```

Fully static reflection
--------------------------

In June 2020 I released PHPStan 0.12.26 with [partially static reflection](/blog/zero-config-analysis-with-static-reflection) which brought many benefits to its users.

It was only partial because the full version consumed more resources. As part of optimizing memory consumption, I've rewritten parts of [BetterReflection](https://github.com/Roave/BetterReflection) so that it doesn't hold onto AST nodes when it no longer needs them. I've [described the process here](https://github.com/Roave/BetterReflection/issues/1073).

In testing it looked really promising, the performance difference between runtime and static reflection was negligible. So I decided it was time to take the next step: start rolling out 100% static reflection engine. It already solves various edge cases like [this](https://github.com/phpstan/phpstan/issues/7077) or [this](https://github.com/phpstan/phpstan/issues/7019).

It's now part of [Bleeding Edge](/blog/what-is-bleeding-edge) and my plan is to enable it for everyone during the PHPStan 1.x release cycle even before the next major version. It's a phased rollout so that I can gather and process feedback from early adopters before unleashing it upon everyone else.


Make `isset()` and null coalescing (`??`) operators consistent in regard to unknown properties
--------------------------

This feature was developed by [Yohta Kimura](https://github.com/rajyan). {.text-sm}

For years PHPStan suffered from this glaring inconsistency:

```php
class Foo
{

}

function (Foo $f): void {
    // No error
    echo isset($f->prop) ? $f->prop : 'bar';

    // Error: Access to an undefined property Foo::$prop.
    echo $f->prop ?? 'bar';
};
```

Although the two lines in question are functionally equivalent, PHPStan's behaviour didn't reflect that. The intention was for PHPStan to protect users from typos in property names, but it proven to be more frustrating than useful for most.

The new default behaviour is:

```php
function (Foo $f): void {
    // No error
    echo isset($f->prop) ? $f->prop : 'bar';

    // No error
    echo $f->prop ?? 'bar';
};
```

But if you want the stricter behaviour and have both lines report an error (consistently at last), you can enable it with this option in your `phpstan.neon`:

```neon
parameters:
    checkDynamicProperties: true
```

This is also enabled in [`phpstan-strict-rules`](https://github.com/phpstan/phpstan-strict-rules) 1.2.0 when combined with [Bleeding Edge](/blog/what-is-bleeding-edge).

Full support of PHP 8.1
--------------------------

Previous PHPStan 1.x releases [brought support](/blog/plan-to-support-php-8-1) of various changes and new features made in PHP 8.1. The last thing PHPStan wasn't aware of were changed signatures of built-in PHP functions. Most notably [`fputcsv`](https://www.php.net/manual/en/function.fputcsv.php) gained a new optional parameter, and [some functions were migrated from resources to objects](https://php.watch/articles/resource-object#resource-php81).

Since PHP 8.0 PHPStan uses a [homegrown stubs repository](https://github.com/phpstan/php-8-stubs) extracted directly from [php-src](https://github.com/php/php-src/) - I can rely on them being correct, but I had to find a way to represent PHP 8.1 changes without doubling the repository in size. I've settled on using custom `#[Until]` and `#[Since]` attributes to mark [signatures that changed](https://github.com/phpstan/php-8-stubs/blob/main/stubs/ext/fileinfo/finfo_open.php).

---

Do you like PHPStan and use it every day? [**Consider supporting further development of PHPStan on GitHub Sponsors**](https://github.com/sponsors/ondrejmirtes/). I’d really appreciate it!
