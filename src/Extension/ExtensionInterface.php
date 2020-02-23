<?php

declare(strict_types=1);

namespace LilleBitte\Kernel\Extension;

use LilleBitte\Container\ContainerBuilderInterface;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
interface ExtensionInterface
{
	/**
	 * Build current extension.
	 *
	 * @param ContainerBuilderInterface $container Container object.
	 * @return void
	 */
	public function build(ContainerBuilderInterface $container);

	/**
	 * Boot up current extension.
	 *
	 * @return void
	 */
	public function boot();

	/**
	 * Terminate down current extension.
	 *
	 * @return void
	 */
	public function terminate();
}