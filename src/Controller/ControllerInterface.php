<?php

namespace LilleBitte\Kernel\Controller;

use LilleBitte\Container\ContainerInterface;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
interface ControllerInterface
{
	/**
	 * Set dependency injection container.
	 *
	 * @param ContainerInterface $container Dependency injection container.
	 */
	public function setContainer(ContainerInterface $container);

	/**
	 * Get dependency injection container.
	 *
	 * @return ContainerInterface
	 */
	public function getContainer();
}
