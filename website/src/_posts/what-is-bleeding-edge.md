---
title: "What is Bleeding Edge?"
date: 2021-04-03
tags: guides
---

When developing new features that don't fit in a [minor version](/user-guide/backward-compatibility-promise), or aren't expected to be released right away, software developers have two options what to do:

* Develop the feature in a feature branch
* Develop it in the main branch but have it turned off in the minor release

Feature branches have a lot of downsides in this context. The code has to live detached from the primary development for months, risking code rot and difficult merging and/or rebasing. Additionally, the code just sits there without gathering valuable feedback, so when it's released to all users at once, there might be problems coming from the lack of testing in the wild.

These problems are solved with feature toggles. The code is developed and released in a minor version, but turned off by default. People can opt in to test the new behaviour and file issues, so the features will be in a much better state when released to all users in the next major version.

Bleeding edge users are [often rewarded](https://backendtea.com/post/use-phpstan-bleeding-edge/) with a much more capable analysis much sooner than the rest.

You can opt in to bleeding edge with this section in your `phpstan.neon`:

```neon
includes:
	- phar://phpstan.phar/conf/bleedingEdge.neon
```

When a new feature is added to bleeding edge, it's referenced in the [release notes](https://github.com/phpstan/phpstan/releases/tag/1.10.0).

Here's the current list of features and changes that Bleeding Edge introduces:


Major features
-------------------

* **Array `list` type** ([#1751](https://github.com/phpstan/phpstan-src/pull/1751))
  * Lists are arrays with sequential integer keys starting at 0
  * [Read more](https://phpstan.org/blog/phpstan-1-9-0-with-phpdoc-asserts-list-type#list-type)
* **Lower memory consumption** and **better performance** thanks to breaking up of reference cycles
  * This is a BC break for rules that use `'parent'`, `'next'`, and `'previous'` node attributes. [Learn more »](https://phpstan.org/blog/preprocessing-ast-for-custom-rules)
  * In testing the memory consumption was reduced by 50–70 %.
  * Time-wise analysis is also much faster, especially on big codebases
* Validate inline PHPDoc `@var` tag type against native type
  * Set [`reportWrongPhpDocTypeInVarTag`](https://phpstan.org/config-reference#reportwrongphpdoctypeinvartag) to `true` to have all types validated, not just native ones
  * [Read more](https://phpstan.org/blog/phpstan-1-10-comes-with-lie-detector#validate-inline-phpdoc-%40var-tag-type)
* `checkMissingIterableValueType: false` no longer does anything (https://github.com/phpstan/phpstan-src/commit/50d0c8e23ea85da508ab8481f1ff2c89148cc80b)
* Always report always true conditions, except for last elseif and match arm
  * If you want them reported even for last elseif and match arm, turn on [`reportAlwaysTrueInLastCondition`](https://phpstan.org/config-reference#reportalwaystrueinlastcondition)
* Disable "unreachable branches" rules: UnreachableIfBranchesRule, UnreachableTernaryElseBranchRule, unreachable arm error in MatchExpressionRule
  * Because "always true" is always reported, these are no longer needed
* Stub files validation - detect duplicate classes and functions (https://github.com/phpstan/phpstan-src/commit/ddf8d5c3859c2c75c20f525a0e2ca8b99032373a, https://github.com/phpstan/phpstan-src/commit/17e4b74335e5235d7cd6708eb687a774a0eeead4)
* Check that each trait is used and analysed at least once - level 4 (https://github.com/phpstan/phpstan-src/commit/c4d05276fb8605d6ac20acbe1cc5df31cd6c10b0)


Minor features and bugfixes
-------------------

* IncompatibleDefaultParameterTypeRule for closures
* New `RuleLevelHelper::accepts()` behaviour (https://github.com/phpstan/phpstan-src/commit/941fc815db49315b8783dc466cf593e0d8a85d23)
* Check template type variance in `@param-out` (https://github.com/phpstan/phpstan-src/commit/7ceb19d3b42cf4632d10c2babb0fc5a21b6c8352), https://github.com/phpstan/phpstan/issues/8880#issuecomment-1426971473
* Report dead types even in multi-exception catch ([#2399](https://github.com/phpstan/phpstan-src/pull/2399)), thanks @JanTvrdik!
* `error_log` errors with `message_type=2` ([#2428](https://github.com/phpstan/phpstan-src/pull/2428)), #9380, thanks @staabm!
* InvalidPhpDocTagValueRule: include PHPDoc line number in the error message (https://github.com/phpstan/phpstan-src/commit/a04e0be832900749b5b4ba22e2de21db8bfa09a0)
* Check `filter_input*` type param type ([#2271](https://github.com/phpstan/phpstan-src/pull/2271)), thanks @herndlm!
* Stricter function signature map (https://github.com/phpstan/phpstan-src/commit/06b746d8e72cc0843707896ec161559bb6a81137, [#2163](https://github.com/phpstan/phpstan-src/pull/2163)), #7239, thanks @staabm!
* Specify `Imagick` parameter types ([#2334](https://github.com/phpstan/phpstan-src/pull/2334)), thanks @zonuexe!
* Fix position variance of static method parameters ([#2313](https://github.com/phpstan/phpstan-src/pull/2313)), thanks @jiripudil!
* Check variance of template types in properties ([#2314](https://github.com/phpstan/phpstan-src/pull/2314)), thanks @jiripudil!
* OverridingMethodRule - include template types in prototype declaring class description (https://github.com/phpstan/phpstan-src/commit/ca2c66cc4dff59ba44d52b82cb9e0aa3256240f3)
* Empty `skipCheckGenericClasses` (https://github.com/phpstan/phpstan-src/commit/28c2c79b16cac6ba6b01f1b4d211541dd49d8a77)
* Fix invariance composition ([#2054](https://github.com/phpstan/phpstan-src/pull/2054)), thanks @jiripudil!
* Improve error wording of the NonexistentOffset, BooleanAndConstantConditionRule, and BooleanOrConstantConditionRule ([#1882](https://github.com/phpstan/phpstan-src/pull/1882)), thanks @VincentLanglet!
* MissingMagicSerializationMethodsRule ([#1711](https://github.com/phpstan/phpstan-src/pull/1711)), #7482, thanks @staabm!
* Unescape strings in phpdoc-parser (https://github.com/phpstan/phpstan-src/commit/97786ed8376b478ec541ea9df1c450c1fbfe7461)
* Report narrowing `PHPStan\Type\Type` interface via `@var` (https://github.com/phpstan/phpstan-src/commit/713b98fb107213c28e3d8c8b4b43c5f5fc47c144), https://github.com/nunomaduro/larastan/issues/1567#issuecomment-1460445389
* Check invalid PHPDocs in previously unchecked statement types (https://github.com/phpstan/phpstan-src/commit/9780d352f3264aac09ac7954f691de1877db8e01)
* InvalidPHPStanDocTagRule in StubValidator (https://github.com/phpstan/phpstan-src/commit/9c2552b7e744926d1a74c1ba8fd32c64079eed61)
* Report useless `array_filter()` calls ([#1077](https://github.com/phpstan/phpstan-src/pull/1077)), #6840, thanks @leongersen!
* ConstantLooseComparisonRule - level 4 (https://github.com/phpstan/phpstan-src/commit/6ebf2361a3c831dd105a815521889428c295dc9f)
* ArrayUnpackingRule (level 3) ([#856](https://github.com/phpstan/phpstan-src/pull/856)), thanks @canvural!
* Rules for checking direct calls to `__construct()` (level 2) ([#1208](https://github.com/phpstan/phpstan-src/pull/1208)), #7022, thanks @muno92!
* Specify explicit mixed array type via `is_array` ([#1191](https://github.com/phpstan/phpstan-src/pull/1191)), thanks @herndlm!
* Unresolvable parameters ([#1319](https://github.com/phpstan/phpstan-src/pull/1319)), thanks @rvanvelzen!
* Support `@readonly` property and `@immutable` class PHPDoc ([#1295](https://github.com/phpstan/phpstan-src/pull/1295), [#1335](https://github.com/phpstan/phpstan-src/pull/1335)), #4082, thanks @herndlm!
* Check that PHPStan class in class constant fetch is covered by backward compatibility promise (https://github.com/phpstan/phpstan-src/commit/9e007251ce61788f6a0319a53f1de6cf801ed233)
* Check code in custom PHPStan extensions for runtime reflection concepts like `is_a()` or `class_parents()` (https://github.com/phpstan/phpstan-src/commit/c4a662ac6c3ec63f063238880b243b5399c34fcc)
* Check code in custom PHPStan extensions for runtime reflection concepts like `new ReflectionMethod()` (https://github.com/phpstan/phpstan-src/commit/536306611cbb5877b6565755cd07b87f9ccfdf08)
* ApiInstanceofRule
  * Report `instanceof` of classes not covered by backward compatibility promise (https://github.com/phpstan/phpstan-src/commit/ff4d02d62a7a2e2c4d928d48d31d49246dba7139)
  * Report `instanceof` of classes covered by backward compatibility promise but where the assumption might change (https://github.com/phpstan/phpstan-src/commit/996bc69fa977aa64f601bd82b8a0ae39be0cbeef)
* Use explicit mixed for global array variables ([#1411](https://github.com/phpstan/phpstan-src/pull/1411)), thanks @herndlm!
* PHPDoc parser: Require whitespace before description with limited start tokens (https://github.com/phpstan/phpdoc-parser/pull/128), https://github.com/phpstan/phpdoc-parser/issues/125, thanks @rvanvelzen!
* Add `@readonly` rule that disallows default values ([#1391](https://github.com/phpstan/phpstan-src/pull/1391)), thanks @herndlm!
* Change `curl_setopt` function signature based on 2nd arg ([#1719](https://github.com/phpstan/phpstan-src/pull/1719)), thanks @staabm!
