<?php

declare(strict_types=1);

namespace Jenky\Bundle\ApiError\Tests\Transformer;

use Jenky\ApiError\Rfc7807Problem;
use Jenky\ApiError\Transformer\ExceptionTransformer;
use Jenky\Bundle\ApiError\Tests\KernelTestCase;
use Nyholm\BundleTest\TestKernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class TransformerTest extends KernelTestCase
{
    public function test_custom_exception_transformer(): void
    {
        self::bootKernel(['config' => static function (TestKernel $kernel) {
            $kernel->addTestConfig(static fn (ContainerBuilder $builder) => $builder->register(ValidationFailedExceptionTransformer::class)
                ->setAutowired(true)
                ->setAutoconfigured(true)
            );
        }]);

        $container = static::getContainer();

        $this->assertTrue($container->has(ValidationFailedExceptionTransformer::class));
        $this->assertTrue($container->has(ExceptionTransformer::class));

        $exception = new ValidationFailedException(
            'foo',
            ConstraintViolationList::createFromMessage('message')
        );

        $problem = $container->get(ExceptionTransformer::class)->transform($exception);

        $this->assertInstanceOf(Rfc7807Problem::class, $problem);
    }
}
