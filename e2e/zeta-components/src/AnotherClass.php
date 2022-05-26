<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\Serializer\SerializerInterface;

final class AnotherClass
{
	private SerializerInterface $serializer;

	public function __construct(SerializerInterface $serializer)
	{
		$this->serializer = $serializer;
	}

	/** @param object $object */
	public function serialize($object): string
	{
		return $this->serializer->serialize($object, 'xml', [
			'xml_root_node_name' => 'xxx',
			'xml_encoding' => 'UTF-8',
		]);
	}
}
