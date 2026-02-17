---
title: "nette.rethrowException"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use Nette\Application\AbortException;
use Nette\Application\UI\Presenter;

class ProductPresenter extends Presenter
{
	public function actionRedirect(): void
	{
		try {
			$this->redirect('Homepage:');
		} catch (\Throwable $e) {
			// logged or silently swallowed
		}
	}
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-nette`.

Certain Nette methods (such as `redirect()`, `forward()`, `sendJson()`, `sendResponse()`, and `terminate()` on Presenters) use exceptions for control flow. For example, `redirect()` throws `Nette\Application\AbortException` to terminate the current request and initiate the redirect. If the code wraps such a call in a `try`/`catch` block that catches `\Exception` or `\Throwable` without rethrowing the control-flow exception, the redirect (or other intended action) will silently fail.

## How to fix it

Catch and rethrow the control-flow exception before catching the general exception:

```diff-php
 <?php declare(strict_types = 1);

 use Nette\Application\AbortException;
 use Nette\Application\UI\Presenter;

 class ProductPresenter extends Presenter
 {
 	public function actionRedirect(): void
 	{
 		try {
 			$this->redirect('Homepage:');
-		} catch (\Throwable $e) {
-			// logged or silently swallowed
+		} catch (AbortException $e) {
+			throw $e;
+		} catch (\Throwable $e) {
+			// handle other exceptions
 		}
 	}
 }
```

Or narrow the catch clause so it does not intercept the control-flow exception:

```diff-php
 <?php declare(strict_types = 1);

 use Nette\Application\UI\Presenter;

 class ProductPresenter extends Presenter
 {
 	public function actionRedirect(): void
 	{
 		try {
 			$this->redirect('Homepage:');
-		} catch (\Throwable $e) {
-			// logged or silently swallowed
+		} catch (\InvalidArgumentException $e) {
+			// handle specific exception
 		}
 	}
 }
```
