<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class NameScope
{

	/**
	 * @var string|null
	 */
	private $namespace;

	/**
	 * @var string[] alias(string) => fullName(string)
	 */
	private $uses;

	public function __construct(string $namespace = null, array $uses)
	{
		$this->namespace = $namespace;
		$this->uses = $uses;
	}

	public function resolveStringName(string $name): string
	{
		if (strpos($name, '\\') === 0) {
			return ltrim($name, '\\');
		}

		$nameParts = explode('\\', $name);
		$firstNamePart = $nameParts[0];
		if (isset($this->uses[$firstNamePart])) {
			if (count($nameParts) === 1) {
				return $this->uses[$firstNamePart];
			}
			array_shift($nameParts);
			return sprintf('%s\\%s', $this->uses[$firstNamePart], implode('\\', $nameParts));
		}

		if ($this->namespace !== null) {
			return sprintf('%s\\%s', $this->namespace, $name);
		}

		return $name;
	}

}
