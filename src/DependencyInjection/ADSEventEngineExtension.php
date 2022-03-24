<?php

declare(strict_types=1);

namespace ADS\Bundle\EventEngineBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class ADSEventEngineExtension extends ConfigurableExtension implements PrependExtensionInterface
{
    /**
     * phpcs:ignore Generic.Files.LineLength.TooLong
     * @param array{"event_store": array{"transactional": bool}, "messenger": array<string, string>, "document_store": array{"prefix": string, "id": array{"schema": string}, "transactional": bool}, "entity_namespace": string, "domain_namespace": string, "pdo_dsn": string} $mergedConfig
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('event_engine.yaml');

        $container->setParameter(
            'event_engine.document_store.prefix',
            $mergedConfig['document_store']['prefix']
        );

        $container->setParameter(
            'event_engine.document_store.id.schema',
            $mergedConfig['document_store']['id']['schema']
        );

        $container->setParameter(
            'event_engine.document_store.transactional',
            $mergedConfig['document_store']['transactional']
        );

        $container->setParameter(
            'event_engine.event_store.transactional',
            $mergedConfig['event_store']['transactional']
        );

        $container->setParameter(
            'event_engine.entity_namespace',
            $mergedConfig['entity_namespace']
        );

        $container->setParameter(
            'event_engine.domain_namespace',
            $mergedConfig['domain_namespace']
        );

        $container->setParameter(
            'event_engine.pdo_dsn',
            $mergedConfig['pdo_dsn']
        );

        $container->setParameter(
            'event_engine.messenger.async.transport.command',
            $mergedConfig['messenger']['command_async_transport']
        );

        $container->setParameter(
            'event_engine.messenger.async.transport.event',
            $mergedConfig['messenger']['event_async_transport']
        );

        $container->setParameter(
            'event_engine.messenger.async.transport.query',
            $mergedConfig['messenger']['query_async_transport']
        );
    }

    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $resolvingBag = $container->getParameterBag();
        $configs = $resolvingBag->resolveValue($configs);

        $container->prependExtensionConfig(
            'framework',
            [
                'messenger' => [
                    'default_bus' => 'command.bus',
                    'buses' => [
                        'command.bus' => [],
                        'event.bus' => [],
                        'query.bus' => [],
                    ],
                    'transports' => [
                        'command.async' => $configs['event_engine.messenger.async.transport.command'],
                        'event.async' => $configs['event_engine.messenger.async.transport.event'],
                        'query.async' => $configs['event_engine.messenger.async.transport.query'],
                    ],
                    'routing' => [
                        'ADS\Bundle\EventEngineBundle\Messenger\QueueableCommand' => 'command.async',
                        'ADS\Bundle\EventEngineBundle\Messenger\QueueableEvent' => 'event.async',
                        'ADS\Bundle\EventEngineBundle\Messenger\QueueableQuery' => 'query.async',
                    ],
                ],
            ]
        );
    }
}
