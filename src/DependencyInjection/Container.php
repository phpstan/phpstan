<?php declare(strict_types = 1);

namespace PHPStan\DependencyInjection;

interface Container
{

	/**
	 * @param string $serviceName
	 * @return mixed
	 */
	public function getService(string $serviceName);

	/**
	 * @param string $className
	 * @return mixed
	 */
	public function getByType(string $className);

	/**
	 * @param string $tagName
	 * @return mixed[]
	 */
	public function getServicesByTag(string $tagName): array;

	/**
	 * @param string $parameterName
	 * @return mixed
	 */
	public function getParameter(string $parameterName);

}
