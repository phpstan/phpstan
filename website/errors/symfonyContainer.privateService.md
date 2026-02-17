---
title: "symfonyContainer.privateService"
ignorable: true
---

This error is reported by `phpstan/phpstan-symfony`.

## Code example

```php
<?php declare(strict_types = 1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends AbstractController
{
	public function index(): Response
	{
		$mailer = $this->container->get('app.mailer');

		return new Response('OK');
	}
}
```

## Why is it reported?

The service being fetched from the Symfony dependency injection container is configured as private. Private services cannot be retrieved directly from the container using `get()`. They are only available through dependency injection (autowiring or explicit configuration). Attempting to fetch a private service at runtime will throw an exception.

## How to fix it

Inject the service through the constructor or method injection instead of fetching it from the container.

```diff-php
 <?php declare(strict_types = 1);

 namespace App\Controller;

+use App\Service\Mailer;
 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 use Symfony\Component\HttpFoundation\Response;

 class ProductController extends AbstractController
 {
-	public function index(): Response
+	public function __construct(
+		private Mailer $mailer,
+	) {
+	}
+
+	public function index(): Response
 	{
-		$mailer = $this->container->get('app.mailer');
+		$this->mailer->send();

 		return new Response('OK');
 	}
 }
```
