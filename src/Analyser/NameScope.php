<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Generic\TemplateTypeScope;
use PHPStan\Type\Type;

class NameScope
{

	/** @var string|null */
	private $namespace;

	/** @var string[] alias(string) => fullName(string) */
	private $uses;

	/** @var string|null */
	private $className;

	/** @var string|null */
	private $functionName;

	/** @var TemplateTypeMap */
	private $templateTypeMap;

	/**
	 * @param string|null $namespace
	 * @param string[] $uses alias(string) => fullName(string)
	 * @param string|null $className
	 */
	public function __construct(?string $namespace, array $uses, ?string $className = null, ?string $functionName = null, ?TemplateTypeMap $templateTypeMap = null)
	{
		$this->namespace = $namespace;
		$this->uses = $uses;
		$this->className = $className;
		$this->functionName = $functionName;
		$this->templateTypeMap = $templateTypeMap ?? TemplateTypeMap::createEmpty();
	}

	public function getClassName(): ?string
	{
		return $this->className;
	}

	public function resolveStringName(string $name): string
	{
		if (strpos($name, '\\') === 0) {
			return ltrim($name, '\\');
		}

		$nameParts = explode('\\', $name);
		$firstNamePart = strtolower($nameParts[0]);
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

	public function getTemplateTypeScope(): ?TemplateTypeScope
	{
		if ($this->className !== null) {
			if ($this->functionName !== null) {
				return TemplateTypeScope::createWithMethod($this->className, $this->functionName);
			}

			return TemplateTypeScope::createWithClass($this->className);
		}

		if ($this->functionName !== null) {
			return TemplateTypeScope::createWithFunction($this->functionName);
		}

		return null;
	}

	public function getTemplateTypeMap(): TemplateTypeMap
	{
		return $this->templateTypeMap;
	}

	public function resolveTemplateTypeName(string $name): ?Type
	{
		return $this->templateTypeMap->getType($name);
	}

	public function withTemplateTypeMap(TemplateTypeMap $map): self
	{
		return new self(
			$this->namespace,
			$this->uses,
			$this->className,
			$this->functionName,
			new TemplateTypeMap(array_merge(
				$this->templateTypeMap->getTypes(),
				$map->getTypes()
			))
		);
	}

}
