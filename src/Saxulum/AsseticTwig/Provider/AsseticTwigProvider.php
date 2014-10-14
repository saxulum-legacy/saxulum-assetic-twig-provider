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
use Assetic\Filter\MinifyCssCompressorFilter;
use Assetic\Filter\ScssphpFilter;
use Assetic\FilterManager;
use Saxulum\AsseticTwig\Assetic\Filter\CssCopyFileFilter;
use Saxulum\AsseticTwig\Assetic\Helper\Dumper;
use Saxulum\AsseticTwig\Command\AsseticDumpCommand;

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
            'csscompress' => true,
            'jsmin' => true,
        );

        $container['assetic.filters'] = $container->share(function () use ($container) {
            return array();
        });

        $container['assetic.filterinstances'] = $container->share(function () use ($container) {
            $filterInstances = array();

            $filterConfig = array_merge($container['assetic.filters.default'], $container['assetic.filters']);

            if ($filterConfig['csscopyfile']) {
                $filterInstances['csscopyfile'] = new CssCopyFileFilter(
                    $container['assetic.asset.asset_root']
                );
            }

            if ($filterConfig['lessphp'] && class_exists('\lessc')) {
                $filterInstances['lessphp'] = new LessphpFilter();
            }

            if ($filterConfig['scssphp'] && class_exists('\scssc')) {
                $filterInstances['scssphp'] = new ScssphpFilter();
            }

            if ($filterConfig['cssmin'] && class_exists('\CssMin')) {
                $filterInstances['cssmin'] = new CssMinFilter();
            }

            if ($filterConfig['csscompress'] && class_exists('\Minify_CSS_Compressor')) {
                $filterInstances['csscompress'] = new MinifyCssCompressorFilter();
            }

            if ($filterConfig['jsmin'] && class_exists('\JSMin')) {
                $filterInstances['jsmin'] = new JSMinFilter();
            }

            return $filterInstances;
        });

        $container['assetic.filter_manager'] = $container->share(function () use ($container) {
            $filterManager = new FilterManager();

            $filters = $container['assetic.filterinstances'];

            foreach ($filters as $alias => $filter) {
                $filterManager->set($alias, $filter);
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

        if (isset($container['console.commands'])) {
            $container['console.commands'] = $container->share(
                $container->extend('console.commands', function ($commands) use ($container) {
                    $commands[] = new AsseticDumpCommand(null, $container);

                    return $commands;
                })
            );
        }

    }
}
