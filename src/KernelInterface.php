<?php

declare(strict_types=1);

namespace LilleBitte\Kernel;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
interface KernelInterface
{
	/**
	 * Register given extensions.
	 *
	 * @return iterable
	 */
	public function registerExtensions(): iterable;

	/**
	 * Register given annotation classes.
	 *
	 * @param array $classes List of classes.
	 * @return void
	 */
	public function registerAnnotationClasses(array $classes);
}
