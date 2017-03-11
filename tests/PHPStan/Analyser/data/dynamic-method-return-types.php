<?php

namespace DynamicMethodReturnTypesNamespace;

class EntityManager
{
    public function getByPrimary(string $className, int $id): Entity
    {
        return new $className();
    }

    public static function createManagerForEntity(string $className): self
    {
    }
}

class InheritedEntityManager extends EntityManager
{
}

class Foo
{
    public function doFoo()
    {
        $em = new EntityManager();
        $iem = new InheritedEntityManager();
        die;
    }
}
