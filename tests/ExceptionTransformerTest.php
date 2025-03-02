<?php

declare(strict_types=1);

namespace CCT\Bundle\ApiProblem\Tests;

use Jenky\ApiError\Rfc7807Problem;
use Jenky\ApiError\Transformer\ChainTransformer;
use Jenky\Bundle\ApiProblem\Transformer\ValidationFailedExceptionTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ExceptionTransformerTest extends TestCase
{
    public function test_chain_exception_transformer(): void
    {
        $transformer = new ChainTransformer([
            new ValidationFailedExceptionTransformer(),
        ]);

        $exception = new ValidationFailedException(
            'foo',
            ConstraintViolationList::createFromMessage('message')
        );

        $problem = $transformer->transform($exception);

        $this->assertInstanceOf(Rfc7807Problem::class, $problem);

        $data = $problem->toRepresentation();

        $this->assertIsArray($data);
        $this->assertCount(1, $data['invalid-params']);
        $this->assertSame(422, $problem->getStatusCode());

        $problem = $transformer->transform(new \InvalidArgumentException());
        $this->assertSame(500, $problem->getStatusCode());
    }

    public function test_validation_exception_transformer(): void
    {
        $exception = new ValidationFailedException(
            'foo',
            ConstraintViolationList::createFromMessage('message')
        );

        $transformer = new ValidationFailedExceptionTransformer();

        $problem = $transformer->transform($exception);
        $this->assertInstanceOf(Rfc7807Problem::class, $problem);

        $data = $problem->toRepresentation();

        $this->assertIsArray($data);
        $this->assertCount(1, $data['invalid-params']);
    }
}
