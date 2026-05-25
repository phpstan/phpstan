---
title: "PHPStan 2.2: Unsealed Array Shapes, Safer Array Keys, and More!"
date: 2026-05-28
tags: releases
---

It's been almost a year and a half since we last bumped the minor version. Although we released many improvements in the 2.1.x series, grouped together in over 55 releases, we also cooked up some bigger changes people have been asking for for a long time.

Two of them have stood out ever since array shapes were [first introduced seven years ago](https://github.com/phpstan/phpstan-src/commit/c4ed3c7c8ded963aa8bec0c6c789a5090998f5c9):

* The ability to intersect an array shape with a general array: `array<string, X>&array{a: Y}` - meaning `array{a: Y}` plus the possibility of more `string` keys whose values are `X`.
* The ability to intersect two distinct array shapes like `array{a: X}&array{b: Y}` to produce one combined shape `array{a: X, b: Y}`. The motivating use cases are composable type aliases, and modelling a function that returns its input array with an extra key added.

The problem with both of those is that they don't make sense from the type system's point of view. The type system is a mathematical system where hacks and workarounds lead to leaks and collapse quickly. Elements of an [intersection type](/blog/union-types-vs-intersection-types) get [normalized](/developing-extensions/type-system#type-normalization) to the type that's the smaller one out of a pair (the subtype). So `mixed&int` results in `int` and `int&int<0, max>` results in `int<0, max>`. With this definition `array<string, X>&array{a: Y}` gets normalized to `array{a: Y}`. And `array<int, X>&array{a: Y}` would be normalized to `never` type (the bottom type) because there's no overlap (no intersection) between these two types.

The intersection of `array{a: X}` and `array{b: Y}` is equivalent to having this code:

```php
// $x is X, $y is Y
$a = ['a' => $x];
$b = ['b' => $y];
if ($a === $b) {
    // here we'd have the intersection
    // but in reality these types are distinct and do not overlap
    // the condition is always false!
}
```

These reasons are why these operations didn't work as expected. But I acknowledge the real need for them. In order to make them work, we'd have to introduce a new concept and new syntax.

Introducing unsealed array shapes
-------------------

The intersection `array{a: X}&array{b: Y}` would make sense if the array shapes allowed for extra keys. So this is the syntax:

- `array{foo: int, ...<string, int>}` - constrain the extra keys' key and value types
- `array{foo: int, ...<Bar>}` - a single type inside `...<>` is the value type (key is implied to be `array-key` which is a [benevolent union](/config-reference#checkbenevolentuniontypes) of `int|string`)
- `list{X, Y, ...<Z>}` - lists only allow a single value type after first two keys (the key is always `int`)
- `array{foo: int, ...}` - bare `...` means any extra keys (shorthand for `...<array-key, mixed>`). Please not that bare `...` does not pass PHPStan's level 6 and you have to explicitly state the value type.

I consider this very intuitive because the part after `...` works the same way in general arrays like `array<string, int>`. [^syntaxOrigin]

[^syntaxOrigin]: When writing this article, I went down the memory lane and tried to find the origin of this syntax. The whole concept was initially discussed on Psalm's GitHub but [my idea](https://github.com/vimeo/psalm/issues/4700#issuecomment-869987365) was more TypeScript-like: `array{foo: Foo, [k: string]: Bar}`. Then I found this [issue](https://github.com/vimeo/psalm/issues/8804) from Vincent Langlet (now a major PHPStan contributor!) asking to officially support the syntax in PHPDocs (Matt Brown originally only used it in Psalm's error messages). So I guess that's where it started.

The great news is that this solves both problems. We can now describe array shapes with extra keys. We can also produce a combined shape by intersecting unsealed array shapes. When one or both sides allow for extra keys, the intersection suddenly makes sense!

* `array{a: int, ...}&array{b: string, ...}` leads to `array{a: int, b: string, ...}`
* `array{a: int, b: string, ...}&array{a: int<0, max>, ...}` leads to `array{a: int<0, max>, b: string, ...}`
* `array{a: int, ...}&array{a: string, ...}` still leads to `never` - same known key with incompatible value types.


Sealed array shapes become truly sealed
-------------------

PHPStan's modelling of array shapes has been flawed for the past seven years. They allow extra keys but they are inconsistent about it.

Let's take a function:

```php
/**
 * @param array{a: int} $a
 */
function doFoo(array $a): void { ... }
```

PHPStan doesn't check whether the argument has extra keys, so calls with `array{a: int, b: string}` are accepted as well.

PHPStan even accepts a general array as long as we check that key `a` is present:

```php
/**
 * @param array<int> $b
 */
function doBar(array $b): void {
    if (isset($b['a'])) {
        doFoo($b); // No errors!
    }
}
```

And that's not okay, because the array shape contradicts itself the moment we start asking it questions:

```php
/**
 * @param array{a: int} $a
 */
function doFoo(array $a): void {
    \PHPStan\dumpType(count($a)); // reports 1, but that's a lie
    echo $a['b'] ?? 'foo'; // PHPStan reports the offset doesn't exist - also a lie, b may well exist at runtime
    foreach ($a as $k => $v) {
        \PHPStan\dumpType($k); // Reports 'a', but the user might have passed more keys
        \PHPStan\dumpType($v); // Reports int, but should be mixed
    }
}
```

Array shapes look great on the surface - they let legacy applications describe the arrays flowing through their code. But under the hood they behave inconsistently and unsafely, leading to undetected runtime errors.

These mistakes are remedied in PHPStan 2.2 when you turn on [bleeding edge](/blog/what-is-bleeding-edge). Array shapes like `array{a: int}` become truly sealed and report everything consistently. You can no longer pass general arrays to them and you can't pass extra keys either.

For now it's opt-in, and it will become the only behaviour in the next major PHPStan version. It's a big disruption - real-world projects will typically see hundreds of new errors when they turn this on. These errors are valuable and important to fix, but we decided to gate it behind bleeding edge because of PHPStan's [backward compatibility promise](/user-guide/backward-compatibility-promise).

When a sealed array shape rejects another array, the rejection comes with a reason, shown next to the error message (with a 💡 emoji prefix in the default `table` formatter).

![Sealed array tip](/images/sealed-tip.png)

These tips also appear on the [online playground](/r/716542aa-66ae-453f-969e-4cb71a2ecb3c) and in [PHPStan Pro](/blog/introducing-phpstan-pro){.phpstan-pro-label}. Some examples:

* Sealed array shape can only accept a constant array. Extra keys are not allowed.
* Sealed array shape does not accept array with extra key 'year'.
* Sealed array shape does not accept unsealed array shape.

These messages will help users understand why their types are rejected.

Unsealed array shapes and related work **have been commissioned and made possible by the [Sovereign Tech Fund](https://www.sovereign.tech/)**.

Safer array keys
------------------

Changes to array shapes are not the only thing added to PHPStan 2.2.0, and not even the only thing related to arrays. We're also trying to tackle a long-standing PHP quirk around string array keys. This has been extensively described in the recent article [Why Array String Keys Are Not Type-Safe in PHP](/blog/why-array-string-keys-are-not-type-safe).

The TL;DR: you can't rely on the key type in `array<string, X>`. Even if you only use strings as keys there, you might end up with an array that's `array<int|string, X>`. Specifically, strings that contain decimal integers (like `'123'`) get cast to integers.

PHPStan 2.2.0 offers several countermeasures to this problem. It introduces the `non-decimal-int-string` type which describes strings that don't get cast to `int` when used as array keys. You can't use an arbitrary `string` as a `non-decimal-int-string`, because it might be cast to `int` at runtime.

```php
/** @param array<non-decimal-int-string, mixed> $data */
function processData(array $data): void { ... }

function doFoo(string $s)
{
    $a = [];
    $a[$s] = 1; // might produce an int key

    // Error:
    // Parameter #1 $data of function processData
    // expects array<non-decimal-int-string, mixed>, non-empty-array<string, 1> given.
    processData($a);
}
```

Use `non-decimal-int-string` for the array keys where type safety matters most, and PHPStan will enforce it. For completeness, we're also introducing `decimal-int-string` which also no doubt [has its uses](/r/d46cc2dd-07b5-482f-8b48-3697580f4968).

Additionally, PHPStan 2.2.0 introduces a new configuration parameter [`reportUnsafeArrayStringKeyCasting`](/config-reference#reportunsafearraystringkeycasting) with three possible values that change the interpretation of PHPDoc types:

* **`null`** - Current behaviour. No additional checks. String keys that look like decimal integers are silently cast to `int` at runtime without any PHPStan errors.
* **`'detect'`** - A typehinted `array<string, mixed>` accepts other `array<string, mixed>`, but when you get a key out of it (e.g. via `foreach` or `array_keys`), the key type will be `int|non-decimal-int-string` instead of `string`. This reveals potential issues without requiring you to change all your array type annotations.
* **`'prevent'`** - A typehinted `array<string, mixed>` is essentially treated as `array<non-decimal-int-string, mixed>`. It will only accept another array with safe string keys. Additionally, any `string` being used as an array key is correctly narrowed to `int|non-decimal-int-string`.

For now the default is `null`. Time and prolonged exposure to these options will tell whether we should change the default to one of the stricter behaviours. From my experience, both of them are brutal in their own ways and cause hundreds of new errors in real-world projects.


Checking constants passed as function arguments
-----------------

Try to spot an error in this code:

```php
function (string $s): void {
    $json = json_decode($s, true, JSON_THROW_ON_ERROR);
};
```

Seems correct, doesn't it? Except `JSON_THROW_ON_ERROR` is being passed as `$depth`, not `$flags`. So this code decodes the JSON with depth set to `4194304`, but it still won't throw on a syntax error.

In order to recognize these errors, we had to extract data from various places (mostly PHP documentation) about which constants are valid for which function parameters. The result is yet another [multi-thousand-line map](https://github.com/phpstan/phpstan-src/blob/2.2.x/resources/constantToFunctionParameterMap.php). The rule's logic is simple: when it sees a call to a function or method in this map and one of its arguments is a built-in PHP constant, the constant is checked against the map entry.

Some examples of what is and isn't reported:

```php
// Error:
// Constant JSON_THROW_ON_ERROR is not allowed for parameter #3 $depth of function json_decode.
json_decode($s, true, JSON_THROW_ON_ERROR);

// Not an error: correct usage
json_decode($s, true, flags: JSON_THROW_ON_ERROR);

// Error:
// Constant SORT_NATURAL is not allowed for parameter $flags of function json_decode.
json_decode($s, true, flags: SORT_NATURAL);

// No errors: our constants, not built-in
json_decode($s, true, depth: self::DEPTH, flags: self::THROW);
```

The map also knows where bitmasks are allowed, and which combinations of flags are invalid within them:

```php
// Error:
// Combining constants with | is not allowed for parameter #2 $component of function parse_url.
parse_url($s, PHP_URL_SCHEME | PHP_URL_HOST);

// No error - expected usage
flock($f, LOCK_SH | LOCK_NB);

// Error:
// Constants LOCK_SH, LOCK_EX cannot be combined for parameter #2 $operation of function flock.
flock($f, LOCK_SH | LOCK_EX);
```

This has historically been a blind spot in PHPStan. Not anymore. These easy-to-get-wrong code snippets are now reported in PHPStan 2.2.0!


Detecting named arguments with renamed parameters in subtypes
-----------

Let's take the following code:

```php
interface Foo
{
	public function doFoo(int $order): void;
}

class FooImpl implements Foo
{
	public function doFoo(int $number): void { ... }
}
```

Nothing wrong with this code, right? And there isn't, as long as you call it without named arguments:

```php
function (Foo $foo) {
	$foo->doFoo(1); // OK
};
```

But if you use named arguments, you suddenly have a bug:

```php
$cb = function (Foo $foo) {
	$foo->doFoo(order: 1); // correct parameter name for Foo::doFoo() method
};

// PHP: Fatal error: Uncaught Error: Unknown named parameter $order
$cb(new FooImpl());
```

For the longest time [^php8] I've resisted reporting the renamed parameter in an overridden method as a bug, I didn't want to bother developers with that. After all, it isn't a bug if you never use named arguments, they're still a pretty rare occurrence in real-world code.

[^php8]: Ever since PHP 8.0 was released.

But if PHPStan 2.2.0 [detects](/error-identifiers/argument.parameterRenamedInSubtype) that you're calling a method on an interface or non-final class with named arguments, and the parameter is renamed in an overriding method in a subclass, it's certain you have a bug. You either need to drop the named argument (and pass it positionally), or sync the parameter names. You'll see this error message:

> Call to Foo::doFoo() uses named argument for parameter $order, but FooImpl renames it to $number.

Reducing false positives about constant conditions in traits
-----------

PHPStan [analyses traits](/blog/how-phpstan-analyses-traits) in the context of each class that uses them. An unused trait is not analysed at all; a trait used in three classes is analysed three times. This approach benefits codebases that use traits heavily. Your code in traits can reference properties and methods declared only on the using classes, and as long as it lines up, PHPStan doesn't report any false positives.

But there's another downside to this approach. PHPStan still complained about things that only make sense to flag in code that isn't reused.

In normal code it might make sense to ask about `$this->getFoo() !== null` if the return type is `string|null`. Totally valid.

But it's different in a trait, when `getFoo` returns a non-nullable type in one of the using classes:

```php
trait Foo
{
	public function getName(): string
	{
		$str = 'Hello';
		if ($this->getFoo() !== null) {
			$str .= ' World';
		}

		return $str;
	}
}

class Bar
{
	use Foo;

	public function getFoo(): string
	{
		return "Bar";
	}
}

class Zar
{
	use Foo;

	public function getFoo(): ?string
	{
		return rand(0, 1) ? 'foo' : null;
	}
}
```

Past PHPStan versions analysed each trait usage in isolation with no regard to other contexts. It'd report:

> PHPStan: Strict comparison using !== between string and null will always evaluate to true.
> (in the context of class Bar)

In PHPStan 2.2.0 we updated a group of rules reporting "constant conditions" to defer these errors until the trait has been analysed in all contexts. If the reports differ across contexts, no error is reported.

If the condition is always true or always false in all contexts, it's still reported. So code like this is still reported even when it lives in a trait:

```php
$o = new stdClass();

// PHPStan: If condition is always true.
if ($o) {

}
```



Allowing custom rules to emit collector data
-----------

The community has taken great advantage of [collectors](/developing-extensions/collectors), implementing awesome extensions like [dead-code-detector](https://github.com/shipmonk-rnd/dead-code-detector). Collectors work exactly like [custom rules](/developing-extensions/rules), but instead of reporting errors directly, they collect data for later evaluation. At the end of the analysis, developers can look at all the collected data from the entire codebase at once, and decide what errors should be reported.

In past PHPStan versions, you'd have to decide whether you're writing a custom rule or a collector. What PHPStan 2.2.0 brings to the table is that a custom rule can easily become a collector too. It can report some errors directly, and defer others to later evaluation. All thanks to the [CollectedDataEmitter](https://apiref.phpstan.org/2.2.x/source-src.Analyser.CollectedDataEmitter.html#32) interface, implemented by `Scope`.

Instead of the usual `processNode` signature:

```php
public function processNode(Node $node, Scope $scope): array
```

You can also typehint with the new interface:

```php
/**
 * @param Scope&CollectedDataEmitter $scope
 */
public function processNode(Node $node, Scope $scope): array
```

And emit collected data for an existing collector class:

```php
$scope->emitCollectedData(MyCollector::class, ['some', 'data']);
```

This is what made the previous two features in this article possible in the first place!

---

Do you like PHPStan and use it every day? [**Consider sponsoring** further development of PHPStan on GitHub Sponsors and also **subscribe to PHPStan Pro**](/sponsor)! I'd really appreciate it!
