<?php

declare(strict_types=1);

namespace Jenky\Bundle\ApiError;

use Jenky\ApiError\Formatter\AbstractErrorFormatter;
use Jenky\ApiError\Formatter\ErrorFormatter;
use Jenky\ApiError\Formatter\GenericErrorFormatter;
use Jenky\ApiError\Formatter\Rfc7807ErrorFormatter;
use Jenky\ApiError\Handler\Symfony\JsonResponseHandler;
use Jenky\ApiError\Handler\Symfony\ResponseHandler;
use Jenky\ApiError\Transformer\ChainTransformer;
use Jenky\ApiError\Transformer\ExceptionTransformer;
use Jenky\Bundle\ApiError\EventListener\ExceptionListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class ApiErrorBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->registerForAutoconfiguration(ExceptionTransformer::class)
            ->addTag('api_error.exception_transformer');

        $container->services()
            ->set('api_error.exception_transformer.chain', ChainTransformer::class)
            ->args([
                Configurator\tagged_iterator('api_error.exception_transformer'),
            ])
            ->alias(ExceptionTransformer::class, 'api_error.exception_transformer.chain')

            ->set(JsonResponseHandler::class)
            ->args([
                Configurator\service(ErrorFormatter::class),
            ])

            ->set('api_error.exception_listener', ExceptionListener::class)
            ->args([
                Configurator\service(ResponseHandler::class),
            ])
            ->tag('kernel.event_listener', ['priority' => -120])

            ->set(AbstractErrorFormatter::class)
            ->abstract()
            ->args([
                Configurator\param('kernel.debug'),
                Configurator\service(ExceptionTransformer::class),
            ]);

        $formatters = [
            'api_error.error_formatter.generic' => GenericErrorFormatter::class,
            'api_error.error_formatter.rfc7870' => Rfc7807ErrorFormatter::class,
        ];

        foreach ($formatters as $id => $formatter) {
            $builder->registerChild($id, AbstractErrorFormatter::class)
                ->setClass($formatter);
        }

        if (! $builder->hasDefinition(ErrorFormatter::class)) {
            $builder->setAlias(ErrorFormatter::class, 'api_error.error_formatter.generic');
        }

        if (! $builder->hasDefinition(ResponseHandler::class)) {
            $builder->setAlias(ResponseHandler::class, JsonResponseHandler::class);
        }
    }
}
