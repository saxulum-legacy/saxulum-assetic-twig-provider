<?php

namespace Saxulum\AsseticTwig\Cilex\Provider;

use Saxulum\AsseticTwig\Provider\AsseticTwigProvider as BaseAsseticTwigProvider;
use Cilex\Application;
use Cilex\ServiceProviderInterface;

class AsseticTwigProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $pimpleServiceProvider = new BaseAsseticTwigProvider();
        $pimpleServiceProvider->register($app);
    }
}
