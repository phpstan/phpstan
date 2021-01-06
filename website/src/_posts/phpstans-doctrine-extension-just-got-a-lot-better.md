---
title: "PHPStan’s Doctrine extension just got a lot better!"
date: 2019-02-13
tags: releases
---

[PHPStan is a static analyser](/blog/find-bugs-in-your-code-without-writing-tests) that focuses on finding bugs in your code before you even run it. Its unique extensibility allows it to correctly interpret common magic behavior of PHP like `__call()`, `__set()`, `__get()`, and offer static analysis in areas where it was considered impossible.

[Doctrine ORM](https://www.doctrine-project.org/) is a popular library that allows developers to represent data from the database as objects in their application.

Today is the day they are starting to be meant for each other.

---

I released a new version of [phpstan-doctrine](https://github.com/phpstan/phpstan-doctrine) extension that shifts developing with Doctrine to a whole new level. What does it bring to the table?

## Static DQL validation!

Every time you change the definition of your Doctrine entity, you’re risking breaking your application. When removing or renaming persistent fields, you have to go through your whole application and check all the DQL queries whether they don’t reference anything that no longer exists.

But phpstan-doctrine has you covered. It will check the syntax and all referenced entities, associations and fields in all recognized DQL queries in the whole codebase!

![phpstan-doctrine](/images/phpstan-doctrine.png)

## Validate method calls on EntityRepository

PHPStan now knows everything about your entities. Besides DQL validation, it can take advantage of the newly obtained knowledge also in other ways.

If you find yourself often using methods on EntityRepository like this:

```php
$entityRepository = $entityManager->getRepository(MyEntity::class);
$entityRepository->findBy(['title' => $title]);
$entityRepository->findByTitle($title);
$entityRepository->findOneBy(['title' => $title]);
$entityRepository->findOneByTitle($title);
$entityRepository->count(['title' => $title]);
$entityRepository->countByTitle($title);
```

PHPStan now searches each of these lines for unknown entity fields!

## Generics-like syntax for EntityRepository in phpDoc

For increase in the type coverage, you can typehint your properties, method parameters and return types with this generics syntax:

```php
/** @var \Doctrine\ORM\EntityRepository<MyEntity> */
private $repository;

public function __construct(EntityManager $em)
{
   $this->repository = $em->getRepository(MyEntity::class);
}
```

It will let PHPStan know about the entity type the repository represents in the rest of the class where the `$repository` property is accessed. That means that PHPStan will know about return types from various "find" methods and it will check for unknown fields in those calls as well.

---

[Upgrade today](https://github.com/phpstan/phpstan-doctrine/releases/tag/0.11.1) and let me know what kinds of errors were lurking in your codebases 😉
