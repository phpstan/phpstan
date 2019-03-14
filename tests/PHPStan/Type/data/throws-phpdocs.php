<?php declare(strict_types = 1);

namespace ThrowsPhpDocs;

use LogicException;
use RuntimeException;

interface Foo
{
	/**
	 * @throws RuntimeException
	 */
	public function throwRuntimeException();

	/**
	 * @throws RuntimeException|LogicException
	 */
	public function throwRuntimeAndLogicException();

	/**
	 * @throws RuntimeException
	 * @throws LogicException
	 */
	public function throwRuntimeAndLogicException2();

	/**
	 * @throws RuntimeException Runtime description
	 * @throws LogicException Logic description
	 */
	public function throwRuntimeAndLogicExceptionDescriptive();
}
