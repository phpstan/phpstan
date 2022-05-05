<?php declare(strict_types=1);

use App\DummyService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()
        ->set('dummy', 'dummy');

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\', __DIR__ . '/../src/')
        ->exclude([__DIR__ . '/../src/{DependencyInjection,Entity,Kernel.php}']);

    $services->set(DummyService::class)
        ->arg('$param', param('dummy'));
};
