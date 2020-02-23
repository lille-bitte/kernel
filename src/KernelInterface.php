<?php

declare(strict_types=1);

namespace LilleBitte\Kernel;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
interface KernelInterface
{
	public function registerExtensions(): iterable;
	public function registerAnnotationClasses(array $classes);
}
