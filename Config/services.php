<?php

declare(strict_types=1);

use Mautic\DependencyInjection\MauticCoreExtension;
use MauticPlugin\OnesignalWeb16Bundle\Api\OnesignalWeb16OneSignalApi;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();
    $excludes = [];
    $services
        ->load('MauticPlugin\\OnesignalWeb16Bundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MauticCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');
    $services->alias('mautic.notification.api', OnesignalWeb16OneSignalApi::class);
    $services->alias('notification_api', 'mautic.notification.api');
};
