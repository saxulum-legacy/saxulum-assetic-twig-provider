<?php

namespace Saxulum\AsseticTwig\Provider;

use Assetic\AssetWriter;
use Assetic\Extension\Twig\AsseticExtension;
use Assetic\Extension\Twig\TwigFormulaLoader;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\LazyAssetManager;
use Assetic\Filter\CssMinFilter;
use Assetic\Filter\JSMinFilter;
use Assetic\Filter\LessphpFilter;
use Assetic\Filter\ScssphpFilter;
use Assetic\FilterManager;
use Saxulum\AsseticTwig\Assetic\Filter\CssCopyFileFilter;
use Saxulum\AsseticTwig\Assetic\Helper\Dumper;
use Saxulum\AsseticTwig\Command\AsseticDumpCommand;
use Symfony\Component\Console\Application as ConsoleApplication;

class AsseticTwigProvider
{
    /**
     * @param \Pimple $container
     */
    public function register(\Pimple $container)
    {
        $container['assetic.asset.root'] = '';

        $container['assetic.asset.asset_root'] = '';

        $container['assetic.asset.factory'] = $container->share(function () use ($container) {
            $assetFactory = new AssetFactory($container['assetic.asset.root']);
            $assetFactory->setDefaultOutput($container['assetic.asset.asset_root']);
            $assetFactory->setDebug(isset($container['debug']) ? $container['debug'] : false);
            $assetFactory->setFilterManager($container['assetic.filter_manager']);

            return $assetFactory;
        });

        $container['assetic.filters.default'] = array(
            'csscopyfile' => true,
            'lessphp' => true,
            'scssphp' => true,
            'cssmin' => true,
            'jsmin' => true,
        );

        $container['assetic.filters'] = array();

        $container['assetic.filter_manager'] = $container->share(function () use ($container) {
            $filterManager = new FilterManager();

            $filterConfig = array_merge(
                $container['assetic.filters.default'],
                $container['assetic.filters']
            );

            if ($filterConfig['csscopyfile']) {
                $filterManager->set('csscopyfile', new CssCopyFileFilter(
                    $container['assetic.asset.asset_root']
                ));
            }

            if ($filterConfig['lessphp'] && class_exists('\lessc')) {
                $filterManager->set('lessphp', new LessphpFilter());
            }

            if ($filterConfig['scssphp'] && class_exists('\scssc')) {
                $filterManager->set('scssphp', new ScssphpFilter());
            }

            if ($filterConfig['cssmin'] && class_exists('\CssMin')) {
                $filterManager->set('cssmin', new CssMinFilter());
            }

            if ($filterConfig['jsmin'] && class_exists('\JSMin')) {
                $filterManager->set('jsmin', new JSMinFilter());
            }

            return $filterManager;
        });

        $container['assetic.asset.manager'] = $container->share(function () use ($container) {
            $assetManager = new LazyAssetManager($container['assetic.asset.factory']);
            $assetManager->setLoader('twig', new TwigFormulaLoader($container['twig']));

            return $assetManager;
        });

        $container['assetic.asset.writer'] = $container->share(function () use ($container) {
            return new AssetWriter($container['assetic.asset.asset_root']);
        });

        $container['assetic.asset.dumper'] = $container->share(function () use ($container) {
            return new Dumper(
                $container['twig.loader.filesystem'],
                $container['assetic.asset.manager'],
                $container['assetic.asset.writer']
            );
        });

        $container['twig'] = $container->share(
            $container->extend('twig', function (\Twig_Environment $twig) use ($container) {
                $twig->addExtension(new AsseticExtension($container['assetic.asset.factory']));

                return $twig;
            })
        );

        if(isset($container['console.commands'])) {
            $container['console.commands'] = $container->share(
                $container->extend('console.commands', function ($commands) use ($container) {
                    $commands[] = new AsseticDumpCommand(null, $container);

                    return $commands;
                })
            );
        }

    }
}
