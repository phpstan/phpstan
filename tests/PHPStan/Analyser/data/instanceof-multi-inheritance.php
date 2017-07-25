<?php

namespace InstanceOfNamespace;

use Exception;
use Throwable;

interface HasType
{
	public function getType(): string;
}

trait ReturnsType
{
	public function getType(): string
	{
		return 'TYPE';
	}
}

class MultiInheritanceFoo
{

	public function handleException(Throwable $error)
	{
		$exception = $error;
		if ($exception instanceof Exception) {
			$hasType = $exception;
			if ($hasType instanceof HasType) {
				$returnsType = $hasType;
				if ($returnsType instanceof ReturnsType) {
					die;
				}
			}
		}
	}

}
