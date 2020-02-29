<?php

declare(strict_types=1);

namespace LilleBitte\Kernel\Extension;

use LilleBitte\Container\ContainerBuilderInterface;

use function strrpos;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
abstract class Extension implements ExtensionInterface
{
	/**
	 * @var string
	 */
	private $name;

	/**
	 * {@inheritdoc}
	 */
	public function build(ContainerBuilderInterface $container)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot()
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function terminate()
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function setExtensionName($name)
	{
		$this->name = $name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getExtensionName()
	{
		if (null === $this->name) {
			$this->parseClassName();
		}

		return $this->name;
	}

    /**
     * {@inheritdoc}
     */
    public function getAnnotationClasses()
    {
    }

    /**
	 * Automatically set class name from child class.
	 *
	 * @return void
	 */
	private function parseClassName()
	{
		$pos = strrpos(static::class, "\\");

		$this->setExtensionName(
			false === $pos
				? static::class
				: substr(static::class, $pos + 1)
		);
	}
}
