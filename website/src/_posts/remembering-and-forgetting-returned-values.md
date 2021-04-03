---
title: "Remembering and forgetting returned values"
date: 2021-04-03
tags: guides
---

PHPStan never used to be very consistent when it comes to which returned values of function calls it should remember:

```php
$person = new Person();

if ($person->getName()) {
    \PHPStan\dumpType($person->getName()); // string|null
}

if ($person->getName() !== null) {
    \PHPStan\dumpType($person->getName()); // string
}
```

This behaviour doesn't make much sense and more or less was a result of a few accidents in the implementation.

Why remember these calls at all? Because developers expect it, mostly when calling getters, even when there's a risk the function will return a different value after the second call.

The [latest PHPStan release](https://github.com/phpstan/phpstan/releases/tag/0.12.83) gives you tools to control this behavior, and also makes it consistent if you enable [bleeding edge](/blog/what-is-bleeding-edge) [^bleeding-edge]:

[^bleeding-edge]: The old behavior is still on by default, mostly for backward compatibility. Even if it falls under the type inference changes according to [backward compatibility promise](/user-guide/backward-compatibility-promise), it's a change in behavior that would make most projects fail the analysis.

```php
// With PHPStan 0.12.83 + bleeding edge

$person = new Person();

if ($person->getName()) {
    \PHPStan\dumpType($person->getName()); // string
}

if ($person->getName() !== null) {
    \PHPStan\dumpType($person->getName()); // string
}
```

There are two new annotations that can be used above function and method declarations: `@phpstan-pure` and `@phpstan-impure`. Additionally, functions and methods that return `void` are also considered impure.

Pure function always returns the same value if its inputs (object state and arguments) are the same, and has no side effects. Impure function has side effects and its return value might change even if the input does not.

Returned function values are remembered only for functions without the `@phpstan-impure` annotation:

```php
/** @phpstan-impure */
function impureFunction(): bool
{
    return rand(0, 1) === 0 ? true : false;
}

if (impureFunction()) {
    \PHPStan\dumpType(impureFunction()); // bool
}
```

If you call an impure method on an object that already has a remembered method value, it will be forgotten:

```php
if ($person->getName() !== null) {
    \PHPStan\dumpType($person->getName()); // string
    $person->setName('John Doe');
    \PHPStan\dumpType($person->getName()); // string|null
}
```

If you pass an object that already has a remembered method value as an argument into an impure function, it will be forgotten:

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
