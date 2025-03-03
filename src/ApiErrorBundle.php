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
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class ApiErrorBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->registerForAutoconfiguration(ExceptionTransformer::class)
            ->addTag('api_error.exception_transformer');

        $builder->register(AbstractErrorFormatter::class)
            ->setAbstract(true)
            ->setArgument('$debug', new Parameter('kernel.debug'))
            ->setArgument('$transformer', new Reference(ExceptionTransformer::class));

        $formatters = [
            'api_error.error_formatter.generic' => GenericErrorFormatter::class,
            'api_error.error_formatter.rfc7807' => Rfc7807ErrorFormatter::class,
        ];

        foreach ($formatters as $id => $formatter) {
            $builder->registerChild($id, AbstractErrorFormatter::class)
                ->setClass($formatter);
        }

        if (! $builder->hasDefinition(ErrorFormatter::class)) {
            $builder->setAlias(ErrorFormatter::class, 'api_error.error_formatter.generic');
        }

        if (! $builder->hasDefinition(ResponseHandler::class)) {
            $builder->setAlias(ResponseHandler::class, 'api_error.response_handler.json');
        }

        $container->services()
            ->set('api_error.exception_transformer.chain', ChainTransformer::class)
            ->args([
                Configurator\tagged_iterator('api_error.exception_transformer'),
            ])
            ->tag('api_error.exception_transformer')
            ->alias(ExceptionTransformer::class, 'api_error.exception_transformer.chain')

            ->set('api_error.response_handler.json', JsonResponseHandler::class)
            ->args([
                Configurator\service(ErrorFormatter::class),
            ])

            ->set('api_error.exception_listener', ExceptionListener::class)
            ->args([
                Configurator\service(ResponseHandler::class),
            ])
            ->tag('kernel.event_listener', ['priority' => -120]);
    }
}
