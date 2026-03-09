---
title: "Remembering and forgetting returned values"
date: 2021-04-03
tags: guides
---

Consider this code:

```php
if ($person->getName() !== null) {
    echo 'Hello ' . $person->getName();
}
```

The developer relies on `$person->getName()` returning the same value when called a second time. I'm not holding it against them because their code will work completely fine in most cases. That's why by default PHPStan doesn't punish you for writing code like this. It assumes that you already checked what the method returns, and it's not going to complain that you might be accessing something that's `null`.

If you prefer this topic explained in video form, I talked about this topic at PHP UK Conference 2024 as part of my "Tuning PHPStan to Maximum Strictness" talk:

<iframe
	src="https://www.youtube.com/embed/j8t5hj-uRxY"
	frameborder="0"
	allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
	allowfullscreen
	class="w-full aspect-video mb-8"
></iframe>

What is function purity?
------------------------

This is about the concept of **pure and impure functions**. Function purity describes how a function behaves.

A function or a method is **pure** if it always returns the same value for the same input, and has no side effects. The input for a method is a combination of the object state and the passed arguments. For a standalone function, it's just about the arguments.

A function is **impure** if its return value might change even when the inputs don't, or if it has side effects. A side effect is usually something like sleeping for a few seconds, accessing the filesystem, accessing the database, printing something to the output, depending on time, or calling `rand()`.

The default behaviour
------------------------

PHPStan's default behaviour is:

* Functions and methods that **return a value** are considered **pure**. This makes the first code example work as expected.
* Functions and methods that **return `void`** are considered **impure**. Think about it: if a void function was pure and couldn't do anything useful with its return value, it couldn't do anything at all. A void function always does some kind of side effect — it changes state, writes to a file, or does something else impure.
* **Constructors** are considered **pure**. In most modern applications, the only thing a constructor does is assign values to properties.

Additionally, calling an impure method on an object **forgets the previously remembered state** of that object.

When remembering works in your favor
------------------------

This is an example where the default behaviour works in your favor:

```php
if ($person->getName() !== null) {
    // $person->getName() is remembered as non-null
    echo 'Hello ' . $person->getName();
}
```

PHPStan remembers that `getName()` was already checked and its return value is narrowed down to `string`. No errors are reported.

When remembering works against you
------------------------

But there are cases where this behaviour works against you. Imagine you're inside a PHPUnit test:

```php
self::assertNull($person->getName());
$person->setName($name);
self::assertNotNull($person->getName());
```

You assert that `getName()` is `null`, then you set the name, and on the next line you check that it's not `null`. But if `setName()` returns a value (like `$this` for a fluent interface), PHPStan considers it pure and it won't cause the remembered state to be forgotten. PHPStan is going to complain:

> Call to method `PHPUnit\Framework\Assert::assertNotNull()` with null will always evaluate to false.

Because of the first `assertNull()`, PHPStan thinks that `getName()` returns `null`. And since nothing impure happened in between, it still thinks `getName()` returns `null` on the third line.

So on one hand remembering works in your favor, but on the other hand it doesn't. I'm showing you this so that you can understand when this situation happens in your code, and what you can do about it.

Overriding the default behaviour
------------------------

There are two annotations that can be used above function and method declarations: `@phpstan-pure` and `@phpstan-impure`.

PHPStan needs these annotations because when it analyses a function call, it doesn't see the function's body. It doesn't look at the implementation — it only looks at the PHPDoc and the signature (parameter types and return types). Going inside the method body at the call site would penalize the performance of the analysis. So we need to tell the analyser what the various properties of functions are with these extra custom PHPDoc tags.

### `@phpstan-impure`

If you have something that looks like a getter — it returns a value — but in fact it has some side effects or depends on some global state, you can mark the method or function as impure:

```php
/**
 * @phpstan-impure
 */
public function getName(): ?string
{
    return rand(0, 1) ? 'John' : null;
}

if ($person->getName() !== null) {
    // calling $person->getName() again
    // will be nullable
    \PHPStan\dumpType($person->getName()); // string|null
}
```

The `@phpstan-impure` annotation tells PHPStan that even if we call `getName()` a second time after narrowing the type, it's possible that it's still going to be `null`.

### `rememberPossiblyImpureFunctionValues: false`

You can also reconfigure your project globally. Instead of annotating individual methods, you can set `rememberPossiblyImpureFunctionValues` to `false` in your configuration file:

```yaml
parameters:
    rememberPossiblyImpureFunctionValues: false
```

This is one of the stricter options you can turn on. It's going to force you to first assign the return value into a variable and then continue working with the variable:

```php
if ($person->getName() !== null) {
    // calling $person->getName() again
    // will be nullable
    \PHPStan\dumpType($person->getName()); // string|null
}

// The solution: use a variable
$name = $person->getName();
if ($name !== null) {
    // $name is remembered as string
    \PHPStan\dumpType($name); // string
}
```

It's not the default because people just expect the original example to still work. See [Config Reference](/config-reference#rememberpossiblyimpurefunctionvalues) for more details.

### `@phpstan-pure`

Once you set `rememberPossiblyImpureFunctionValues` to `false`, most functions are now considered impure. But if you really have a getter and you still want PHPStan to remember what the return value is even after you call it a second time, you can explicitly mark methods and functions as pure:

```php
/**
 * @phpstan-pure
 */
public function getName(): ?string
{
    return $this->name;
}

if ($person->getName() !== null) {
    // calling $person->getName() again
    // will remember it cannot be null
    \PHPStan\dumpType($person->getName()); // string
}
```

Calling an impure method forgets the state
------------------------

If you call an impure method on an object that already has a remembered method value, it will be forgotten:

```php
if ($person->getName() !== null) {
    \PHPStan\dumpType($person->getName()); // string
    $person->setName('John Doe');
    \PHPStan\dumpType($person->getName()); // string|null
}
```

Because `setName()` returns `void`, it's considered impure. It makes PHPStan forget everything it knew about `$person`, so `getName()` goes back to returning `string|null`.

If you pass an object that already has a remembered method value as an argument into an impure function, it will be forgotten too:

```php
if ($person->getName() !== null) {
    \PHPStan\dumpType($person->getName()); // string
    resetPerson($person);
    \PHPStan\dumpType($person->getName()); // string|null
}
```

You might find yourself in a situation when you pass an object into an impure method, but still want to have the state remembered. Like [this piece of code](https://github.com/rectorphp/rector/blob/d2e4c0a5b2c7876784cccf462899988454cda8b2/rules/Defluent/Rector/ClassMethod/NormalToFluentRector.php#L71-L116) from [Rector](https://github.com/rectorphp/rector) codebase (reduced for brevity):

```php
/**
 * @param ClassMethod $node
 */
public function refactor(Node $node): ?Node
{
    if ($node->stmts === null) {
        return null;
    }

    // $node->stmts cannot be null here

    $classMethodStatementCount = count($node->stmts);

    for ($i = $classMethodStatementCount - 1; $i >= 0; --$i) {
        // PHPStan reports:
        // Offset int does not exist on array<PhpParser\Node\Stmt>|null.
        $stmt = $node->stmts[$i];
        $prevStmt = $node->stmts[$i - 1];
        if (! $this->isBothMethodCallMatch($stmt, $prevStmt)) {
            if (count($this->collectedMethodCalls) >= 2) {
                // this is an impure method
                // it will reset that $node->stmts isn't null
                $this->fluentizeCollectedMethodCalls($node);
            }

            continue;
        }
    }

    return $node;
}
```

The solution to this problem is simple. Variables!

```php
/**
 * @param ClassMethod $node
 */
public function refactor(Node $node): ?Node
{
    // save $node->stmts to a variable so it does not reset
    // after impure method call
    $stmts = $node->stmts;
    if ($stmts === null) {
        return null;
    }

    $classMethodStatementCount = count($stmts);

    for ($i = $classMethodStatementCount - 1; $i >= 0; --$i) {
        // No errors!
        $stmt = $stmts[$i];
        $prevStmt = $stmts[$i - 1];
        if (! $this->isBothMethodCallMatch($stmt, $prevStmt)) {
            if (count($this->collectedMethodCalls) >= 2) {
                $this->fluentizeCollectedMethodCalls($node);
            }

            continue;
        }
    }

    return $node;
}
```

Finding dead code and bugs
------------------------

The new behavior will mostly help you find dead code when you ask about the same thing twice in a row:

```php
if ($product->isGiftCard()) {
    // do a thing...
    return;
}

// PHPStan reports:
// If condition is always false.
if ($product->isGiftCard()) {
    // do a different thing...
    return;
}
```

And it will help you find serious bugs. Did you know that if you ask about `is_dir($x)` and similar functions multiple times in a row, it will not look at the filesystem unless you call [`clearstatcache()`](https://www.php.net/manual/en/function.clearstatcache.php) between the calls?

```php
if (is_dir($dir)) {
    return;
}

\PHPStan\dumpType(is_dir($dir)); // false

clearstatcache();

\PHPStan\dumpType(is_dir($dir)); // bool

```

---

Do you like PHPStan and use it every day? [**Consider supporting further development of PHPStan on GitHub Sponsors**](https://github.com/sponsors/ondrejmirtes/). I'd really appreciate it!
