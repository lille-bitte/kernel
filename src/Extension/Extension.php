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
}
