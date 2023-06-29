<?php declare(strict_types=1);


namespace App;


class DummyService
{
	/**
	 * @var string
	 */
	public $param;

	public function __construct(string $param)
	{
		$this->param = $param;
	}
}
