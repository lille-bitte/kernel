<?php

declare(strict_types=1);

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
interface KernelInterface
{
	public function registerExtensions(): iterable;
}
