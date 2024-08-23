---
title: Trinary Logic
---

Many methods in PHPStan do not return a two-state boolean, but a three-state [`PHPStan\TrinaryLogic`](https://apiref.phpstan.org/1.12.x/PHPStan.TrinaryLogic.html) object.

The object can be created by calling one of the static methods:

* `PHPStan\TrinaryLogic::createYes(): self`
* `PHPStan\TrinaryLogic::createMaybe(): self`
* `PHPStan\TrinaryLogic::createNo(): self`
* `PHPStan\TrinaryLogic::createFromBoolean(bool $value): self`

You can ask what value the object represents by calling one of these instance methods:

* `PHPStan\TrinaryLogic::yes(): bool`
* `PHPStan\TrinaryLogic::maybe(): bool`
* `PHPStan\TrinaryLogic::no(): bool`
* `PHPStan\TrinaryLogic::equals(self $other): bool`

The object is immutable. You can combine multiple instances either with AND or OR logical operations. These methods return new instances:

* `PHPStan\TrinaryLogic::and(self ...$operands): self`
* `PHPStan\TrinaryLogic::or(self ...$operands): self`

Usage in practice
-------------------

PHPStan uses trinary logic in many places, especially on the [`PHPStan\Type\Type`](https://apiref.phpstan.org/1.12.x/PHPStan.Type.Type.html) interface (see the article about the [type system](/developing-extensions/type-system)).

For example, let's say we have a `new ObjectType(\Exception::class)` and we ask about `hasMethod('getMessage')`. This method will return TrinaryLogic's `yes` because the method always exists.

If we have `Exception|stdClass` union, `hasMethod('getMessage')` returns `maybe`, because the method is available only on one of the subtypes.

Asking `new ObjectType(\Exception::class)` whether it `hasMethod('doFoo')` returns `no`, because that method does not exist on `Exception`.

Trinary logic is at the core of PHPStan's [rule levels](/user-guide/rule-levels). Up until level 6, most rules ask whether the result is `!no()`, meaning that `yes` and `maybe` are valid answers and no error is reported in that case. On level 7 and up, `yes()` is required, meaning that only `yes` is a valid answer.
