<?php

namespace TestAccessProperties;

class FooAccessProperties
{

	private $foo;

	protected $bar;

	public $ipsum;

}

class BarAccessProperties extends FooAccessProperties
{

	private $foobar;

	public function foo()
	{
		$this->loremipsum; // nonexistent
		$this->foo; // private from an ancestor
		$this->bar;
		$this->ipsum;
		$this->foobar;
		Foo::class;

		$string = 'foo';
		$string->propertyOnString;
	}

}

class BazAccessProperties
{

	public function foo(\stdClass $stdClass)
	{
		$foo = new FooAccessProperties();
		$foo->foo;
		$foo->bar;
		$foo->ipsum;
		if (isset($foo->baz)) {
			$foo->baz;
		}
		isset($foo->baz);
		$foo->baz;
		$stdClass->foo;
		if (!isset($foo->nonexistent)) {
			$foo->nonexistent;
			return;
		}
		$foo->nonexistent;

		$fooAlias = new FooAccessPropertiesAlias();
		$fooAlias->foo;
		$fooAlias->bar;
		$fooAlias->ipsum;

		$bar = new UnknownClass();
		$bar->foo;

		if (!empty($foo->emptyBaz)) {
			$foo->emptyBaz;
		}
		$foo->emptyBaz;
		if (empty($foo->emptyNonexistent)) {
			$foo->emptyNonexistent;
			return;
		}
		$foo->emptyNonexistent;

		isset($foo->anotherNonexistent) ? $foo->anotherNonexistent : null;
		isset($foo->anotherNonexistent) ? null : $foo->anotherNonexistent;
		!isset($foo->anotherNonexistent) ? $foo->anotherNonexistent : null;
		!isset($foo->anotherNonexistent) ? null : $foo->anotherNonexistent;

		empty($foo->anotherEmptyNonexistent) ? $foo->anotherEmptyNonexistent : null;
		empty($foo->anotherEmptyNonexistent) ? null : $foo->anotherEmptyNonexistent;
		!empty($foo->anotherEmptyNonexistent) ? $foo->anotherEmptyNonexistent : null;
		!empty($foo->anotherEmptyNonexistent) ? null : $foo->anotherEmptyNonexistent;

		$doc = new \DOMDocument();
		$doc->firstChild;
		$doc->childNodes[0];

		/** @var \DOMElement $el */
		$el = doFoo();
		$el->textContent;
	}

}

class NullPropertyIssue
{

	/** @var FooAccessProperties|null */
	private $fooOrNull;

	public function doFoo()
	{
		if ($this->fooOrNull !== null) {
			return $this->fooOrNull;
		}

		if (doSomething()) {
			$this->fooOrNull = new FooAccessProperties();
		} else {
			$this->fooOrNull = new FooAccessProperties();
		}

		$this->fooOrNull->ipsum;
	}

}

class IssetIssue
{

	public function doFoo($data)
	{
		$data = $this->returnMixed();

		isset($data['action']['test']) ? 'foo' : 'bar';
		isset($data->element[0]['foo']) ? (string) $data->element[0]['bar'] : '';
		isset($data->anotherElement[0]['code']) ? (string) $data->anotherElement[0]['code'] : '';
	}

	public function returnMixed()
	{

	}

}

class PropertiesWithUnknownClasses
{

	/** @var FirstUnknownClass|SecondUnknownClass */
	private $foo;

	public function doFoo()
	{
		$this->foo->test;
	}

}
