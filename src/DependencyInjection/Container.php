<?php declare(strict_types = 1);

namespace PHPStan\DependencyInjection;

interface Container
{

	public function hasService(string $serviceName): bool;

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
	 * @param string $className
	 * @return string[]
	 */
	public function findServiceNamesByType(string $className): array;

	/**
	 * @param string $tagName
	 * @return mixed[]
	 */
	public function getServicesByTag(string $tagName): array;

	/**
	 * @return mixed[]
	 */
	public function getParameters(): array;

	public function hasParameter(string $parameterName): bool;

	/**
	 * @param string $parameterName
	 * @return mixed
	 * @throws \PHPStan\DependencyInjection\ParameterNotFoundException
	 */
	public function getParameter(string $parameterName);

}
