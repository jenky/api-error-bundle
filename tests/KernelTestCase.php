<?php

declare(strict_types=1);

namespace Jenky\Bundle\ApiError\Tests;

use Jenky\Bundle\ApiError\ApiErrorBundle;
use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as TestCase;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class KernelTestCase extends TestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        /**
         * @var TestKernel $kernel
         */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(ApiErrorBundle::class);
        $kernel->handleOptions($options);

        return $kernel;
    }
}
