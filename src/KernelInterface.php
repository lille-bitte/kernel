<?php

declare(strict_types=1);

namespace LilleBitte\Kernel;

use Psr\Http\Message\RequestInterface;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
interface KernelInterface
{
    /**
     * Handle request object.
     *
     * @param RequestInterface $request Request object.
     * @return void
     */
    public function handle(RequestInterface $request);

    /**
     * Register given extensions.
     *
     * @return iterable
     */
    public function registerExtensions(): iterable;
}
