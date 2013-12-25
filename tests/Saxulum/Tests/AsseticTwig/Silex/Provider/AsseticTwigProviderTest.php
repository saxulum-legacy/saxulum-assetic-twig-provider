<?php

namespace Saxulum\Tests\AsseticTwig\Silex\Provider;

use Saxulum\AsseticTwig\Silex\Provider\AsseticTwigProvider;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;

class AsseticTwigProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testDump()
    {
        $testRoot = realpath(__DIR__ . '/../../../../../environment');

        $app = new Application();
        $app->register(new TwigServiceProvider());

        $app['twig.loader.filesystem'] = $app->share($app->extend('twig.loader.filesystem',
            function(\Twig_Loader_Filesystem $twigLoaderFilesystem) use ($testRoot) {
                $twigLoaderFilesystem->addPath($testRoot. '/views', 'SaxulumAsseticTwig');

                return $twigLoaderFilesystem;
            }
        ));

        $app->register(new AsseticTwigProvider(), array(
            'assetic.asset.root' => $testRoot,
            'assetic.asset.asset_root' => $testRoot . '/assets'
        ));

        //$app->boot();

        $app['assetic.asset.dumper']->dump();
    }
}