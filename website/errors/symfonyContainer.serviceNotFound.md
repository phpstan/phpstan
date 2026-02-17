---
title: "symfonyContainer.serviceNotFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyController extends AbstractController
{
    public function index(): void
    {
        $this->get('unknown_service');
    }
}
```

## Why is it reported?

The service identifier passed to `ContainerInterface::get()` does not match any service registered in the Symfony dependency injection container. This means the call will throw a `ServiceNotFoundException` at runtime.

PHPStan reads the compiled container configuration to know which services are available. If the service ID is not found in the compiled container, this error is reported.

This rule is provided by the [phpstan-symfony](https://github.com/phpstan/phpstan-symfony) package.

## How to fix it

Use a valid service identifier that is registered in the container:

```diff-php
 <?php declare(strict_types = 1);

 namespace App\Controller;

 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

 class MyController extends AbstractController
 {
     public function index(): void
     {
-        $this->get('unknown_service');
+        $this->get('app.my_service');
     }
 }
```

Alternatively, use constructor injection instead of fetching services from the container directly, which is the recommended approach in modern Symfony applications:

```diff-php
 <?php declare(strict_types = 1);

 namespace App\Controller;

 use App\Service\MyService;
 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

 class MyController extends AbstractController
 {
-    public function index(): void
+    public function __construct(private MyService $myService)
     {
-        $service = $this->get('app.my_service');
+    }
+
+    public function index(): void
+    {
+        $this->myService->doSomething();
     }
 }
```
