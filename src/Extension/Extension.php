<?php

declare(strict_types=1);

namespace LilleBitte\Kernel\Extension;

use LilleBitte\Container\ContainerBuilderInterface;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
abstract class Extension implements ExtensionInterface
{
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
