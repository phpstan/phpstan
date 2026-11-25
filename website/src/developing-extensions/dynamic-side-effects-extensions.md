---
title: Dynamic Side Effects Extensions
---

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.3.8</div>

PHPStan knows the side effects of every function and method it calls: a function marked as [`@phpstan-pure` or `@phpstan-impure`](/writing-php-code/phpdocs-basics#impure-functions), a method returning `void`, or a built-in function like `printf()` which prints output. It uses them to:

* report a call on a separate line that has no effect, like `strlen($s);` (identifiers `function.resultUnused`, `method.resultUnused` and `staticMethod.resultUnused`),
* report impure calls in functions marked as `@phpstan-pure`,
* decide whether the [returned value is remembered](/blog/remembering-and-forgetting-returned-values) for the next call with the same arguments,
* decide whether the call forgets what's known about the properties of the object or class it's called on, and about global state like the stat cache.

Some functions and methods have side effects only for some arguments. For example `print_r($value)` prints its argument, but `print_r($value, true)` only returns it. If the side effects of a call depend on the arguments passed to it, you can describe them by writing a dynamic side effects extension.

The implementation is all about applying the [core concepts](/developing-extensions/core-concepts) so check out that guide first and then continue here.

This is [the interface](https://apiref.phpstan.org/__BRANCH__/PHPStan.Type.DynamicMethodSideEffectsExtension.html) for dynamic side effects extension:

```php
namespace PHPStan\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\SideEffects;

interface DynamicMethodSideEffectsExtension
{

	public function getClass(): string;

	public function isMethodSupported(ExtendedMethodReflection $methodReflection): bool;

	public function getSideEffectsFromMethodCall(
		ExtendedMethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): ?SideEffects;

}
```

The returned [`SideEffects`](https://apiref.phpstan.org/__BRANCH__/PHPStan.Reflection.SideEffects.html) replace the side effects the method reflection describes for this one call. Returning `null` keeps the side effects of the reflection. The side effects of the method itself are available with `$methodReflection->getSideEffects()`.

Describing side effects
----------------

The `SideEffects` object is created with one of these static methods:

* `SideEffects::none()`: the call doesn't have any side effects.
* `SideEffects::all()`: the call has side effects, but they're not described more precisely.
* `SideEffects::unknown()`: it's not known whether the call has side effects.
* `SideEffects::create(array $effects, TrinaryLogic $others)`: the call has the listed `$effects`. `$others` says whether it can also have any other effect: pass `TrinaryLogic::createNo()` when the list is complete.

The names of the effects are constants on the `SideEffects` class:

| Constant | Effect name | Meaning |
|---|---|---|
| `WRITES_THIS` | `writes-this` | Writes the properties of the object or the class the method is called on. |
| `WRITES_ARGS` | `writes-args` | Writes the arguments passed to it. |
| `WRITES_GLOBAL` | `writes-global` | Writes global state, like ini settings or the locale. A method call with this effect also forgets what's known about the object or class it's called on. |
| `READS_GLOBAL` | `reads-global` | Reads global state. The result stays the same until the global state is written. |
| `NONDETERMINISTIC` | `nondeterministic` | Returns a different value on each call, like `time()` (`nondeterministic.time`) or `rand()` (`nondeterministic.random`). |
| `IO` | `io` | Input and output: `io.output`, `io.files`, `io.network`, `io.db`, or `io.process`. |
| `SLEEP` | `sleep` | Waits, like `sleep()`. |
| `EXIT` | `exit` | Terminates the script. |
| `WARNS` | `warns` | May emit a warning about its arguments. This is allowed in pure functions. |

Global state can also be named more precisely, like `writes-global.stat-cache` for `clearstatcache()` and `reads-global.stat-cache` for `is_file()`. A call that writes a named part of global state forgets only the remembered results of the calls that read it. See the other constants on the [`SideEffects`](https://apiref.phpstan.org/__BRANCH__/PHPStan.Reflection.SideEffects.html) class for the named parts.

A call forgets global state only as far as the returned side effects list `writes-global` or a part of it.

What the call does with the arguments passed to it is still decided by the reflection, because it's decided while the arguments are processed. The side effects of callables passed to parameters marked with `@pure-unless-callable-is-impure` are also added by PHPStan, so the extension doesn't have to describe them.

An example
----------------

Let's say you have a method with an implementation that looks like this:

```php
/** @phpstan-impure */
public function render(string $template, bool $print = true): string
{
	$html = $this->doRender($template);
	if ($print) {
		echo $html;
	}

	return $html;
}
```

The method is marked as impure because it can print the HTML. So PHPStan doesn't report `$renderer->render('page', false);` on a separate line even though such call doesn't do anything, it reports `$renderer->render('page', false)` in pure functions, and it doesn't remember the returned value.

This is how you'd write the extension that tells PHPStan the method has no side effects when `$print` is `false`:

```php
public function getClass(): string
{
	return \App\Renderer::class;
}

public function isMethodSupported(ExtendedMethodReflection $methodReflection): bool
{
	return $methodReflection->getName() === 'render';
}

public function getSideEffectsFromMethodCall(
	ExtendedMethodReflection $methodReflection,
	MethodCall $methodCall,
	Scope $scope
): ?SideEffects
{
	if (count($methodCall->getArgs()) < 2) {
		return null;
	}

	$printType = $scope->getType($methodCall->getArgs()[1]->value);
	if ($printType->isFalse()->yes()) {
		return SideEffects::none();
	}

	return SideEffects::create([SideEffects::IO_OUTPUT], TrinaryLogic::createNo());
}
```

When `$print` is `false`, the call has no side effects. Otherwise it only prints output, which is more precise than `@phpstan-impure`: the call doesn't forget what's known about the object's properties and global state.

And finally, register the extension in the [configuration file](/config-reference):

```yaml
services:
	-
		class: App\PHPStan\RendererSideEffectsExtension
		tags:
			- phpstan.dynamicMethodSideEffectsExtension
```

Alternatively, register it with the [`#[AutowiredService]` attribute](/developing-extensions/dependency-injection-configuration#attribute-services) on the class. The `phpstan.dynamicMethodSideEffectsExtension` tag is added automatically based on the implemented interface.

There's also analogous functionality for:

* **static methods** using [`DynamicStaticMethodSideEffectsExtension`](https://apiref.phpstan.org/__BRANCH__/PHPStan.Type.DynamicStaticMethodSideEffectsExtension.html) interface and `phpstan.dynamicStaticMethodSideEffectsExtension` service tag. Object instantiations with `new` are also described by this extension as static calls to `__construct`.
* **functions** using [`DynamicFunctionSideEffectsExtension`](https://apiref.phpstan.org/__BRANCH__/PHPStan.Type.DynamicFunctionSideEffectsExtension.html) interface and `phpstan.dynamicFunctionSideEffectsExtension` service tag.
