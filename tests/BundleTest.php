<?php

declare(strict_types=1);

namespace Jenky\Bundle\ApiError\Tests;

use Jenky\ApiError\Formatter\ErrorFormatter;
use Jenky\ApiError\Formatter\GenericErrorFormatter;
use Jenky\ApiError\Formatter\Rfc7807ErrorFormatter;
use Jenky\ApiError\Handler\Symfony\JsonResponseHandler;
use Jenky\ApiError\Handler\Symfony\ResponseHandler;
use Jenky\ApiError\Transformer\ChainTransformer;
use Jenky\ApiError\Transformer\ExceptionTransformer;
use Nyholm\BundleTest\TestKernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class BundleTest extends KernelTestCase
{
    public function test_init(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->assertTrue($container->has('api_error.error_formatter.generic'));
        $this->assertTrue($container->has('api_error.exception_transformer.chain'));
        $this->assertTrue($container->has('api_error.response_handler.json'));
        $this->assertTrue($container->has('api_error.exception_listener'));

        $this->assertInstanceOf(ChainTransformer::class, $container->get(ExceptionTransformer::class));
        $this->assertInstanceOf(GenericErrorFormatter::class, $container->get(ErrorFormatter::class));
        $this->assertInstanceOf(JsonResponseHandler::class, $container->get(ResponseHandler::class));
    }

    public function test_init_with_custom_formatter(): void
    {
        self::bootKernel(['config' => static function (TestKernel $kernel) {
            $kernel->addTestConfig(static fn (ContainerBuilder $builder) => $builder->setAlias(ErrorFormatter::class, 'api_error.error_formatter.rfc7807'));
        }]);

        $container = static::getContainer();

        $this->assertInstanceOf(Rfc7807ErrorFormatter::class, $container->get(ErrorFormatter::class));
    }
}
