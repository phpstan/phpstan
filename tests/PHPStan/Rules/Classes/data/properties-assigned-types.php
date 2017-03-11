<?php declare(strict_types = 1);

namespace PropertiesAssignedTypes;

class Foo extends Ipsum
{

    /** @var string */
    private $stringProperty;

    /** @var int */
    private $intProperty;

    /** @var self */
    private $fooProperty;

    /** @var string */
    private static $staticStringProperty;

    /** @var self[]|Collection */
    private $unionPropertySelf;

    /** @var Bar[]|self */
    private $unionPropertyBar;

    public function doFoo()
    {
        $this->stringProperty = 'foo';
        $this->stringProperty = 1;
        $this->intProperty = 1;
        $this->intProperty = 'foo';
        $this->fooProperty = new self();
        $this->fooProperty = new Bar();
        self::$staticStringProperty = 'foo';
        self::$staticStringProperty = 1;
        Foo::$staticStringProperty = 'foo';
        Foo::$staticStringProperty = 1;
        parent::$parentStringProperty = 'foo';
        parent::$parentStringProperty = 1;
        $this->nonexistentProperty = 'foo';
        $this->nonexistentProperty = 1;
        $this->unionPropertySelf = [new self()];
        $this->unionPropertySelf = new Collection();
        $this->unionPropertySelf = new self();
        $this->unionPropertySelf = [new Bar()];
        $this->unionPropertySelf = new Bar();
        $this->parentStringProperty = 'foo';
        $this->parentStringProperty = 1;
        self::$parentStaticStringProperty = 'foo';
        self::$parentStaticStringProperty = 1;
    }
}

class Ipsum
{

    /** @var string */
    protected $parentStringProperty;

    /** @var string */
    protected static $parentStaticStringProperty;
}
