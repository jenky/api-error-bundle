<?php

declare(strict_types=1);

namespace Jenky\Bundle\ApiError\Transformer;

use Jenky\ApiError\Problem;
use Jenky\ApiError\Rfc7807Problem;
use Jenky\ApiError\Transformer\ExceptionTransformer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ValidationFailedExceptionTransformer implements ExceptionTransformer
{
    public function transform(\Throwable $exception): Problem
    {
        \assert($exception instanceof ValidationFailedException);

        $e = Rfc7807Problem::createFromThrowable($exception, Response::HTTP_UNPROCESSABLE_ENTITY)
            ->setMessage('Validation errors');

        foreach ($exception->getViolations() as $violation) {
            $e->addInvalidParam($violation->getPropertyPath(), (string) $violation->getMessage());
        }

        return $e;
    }
}
