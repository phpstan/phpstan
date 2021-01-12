<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;

class Kernel extends SymfonyKernel
{
    use MicroKernelTrait;

    const CONTEXT_ADMIN = 'admin';

    const CONTEXT_WEBSITE = 'website';

    /**
     * @var string
     */
    private $context = self::CONTEXT_ADMIN;

    /**
     * Overload the parent constructor method to add an additional
     * constructor argument.
     *
     * {@inheritdoc}
     *
     * @param string $environment
     * @param bool $debug
     * @param string $suluContext The Sulu context (self::CONTEXT_ADMIN, self::CONTEXT_WEBSITE)
     */
    public function __construct($environment, $debug, $suluContext = self::CONTEXT_ADMIN)
    {
        if (\property_exists($this, 'name')) {
            $this->name = $suluContext;
        }

        parent::__construct($environment, $debug);
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->addResource(new FileResource($this->getProjectDir() . '/config/bundles.php'));
        $confDir = $this->getProjectDir() . '/config';

        $loader->load($confDir . '/packages/doctrine.yaml');
        $loader->load($confDir . '/packages/sentry.yaml');
    }
}
