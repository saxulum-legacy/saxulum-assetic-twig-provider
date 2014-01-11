<?php

namespace Saxulum\AsseticTwig\Provider;

use Assetic\AssetWriter;
use Assetic\Extension\Twig\AsseticExtension;
use Assetic\Extension\Twig\TwigFormulaLoader;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\LazyAssetManager;
use Saxulum\AsseticTwig\Helper\Dumper;

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

            return $assetFactory;
        });

        $container['twig'] = $container->share(
            $container->extend('twig', function (\Twig_Environment $twig) use ($container) {
                $twig->addExtension(new AsseticExtension($container['assetic.asset.factory']));

                return $twig;
            })
        );

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
    }
}
