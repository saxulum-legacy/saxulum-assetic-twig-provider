<?php

namespace Saxulum\AsseticTwig\Helper;

use Assetic\AssetWriter;
use Assetic\Extension\Twig\TwigResource;
use Assetic\Factory\LazyAssetManager;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Dumper
{
    /**
     * @var \Twig_Loader_Filesystem
     */
    protected $loader;

    /**
     * @var LazyAssetManager
     */
    protected $lam;

    /**
     * @var AssetWriter
     */
    protected $aw;

    /**
     * @param \Twig_Loader_Filesystem $loader
     * @param LazyAssetManager $lam
     * @param AssetWriter $aw
     */
    public function __construct(\Twig_Loader_Filesystem $loader, LazyAssetManager $lam, AssetWriter $aw)
    {
        $this->loader = $loader;
        $this->lam = $lam;
        $this->aw = $aw;
    }

    public function dump()
    {
        $finder = new Finder();
        $twigNamespaces = $this->loader->getNamespaces();

        foreach($twigNamespaces as $ns) {
            if(count($this->loader->getPaths($ns)) > 0 ) {
                $iterator = $finder->files()->in($this->loader->getPaths($ns));
                foreach ($iterator as $file) {
                    /** @var SplFileInfo $file */
                    $resource = new TwigResource($this->loader, $file->getRelativePathname());
                    $this->lam->addResource($resource, 'twig');
                }
            }
        }

        $this->aw->writeManagerAssets($this->lam);
    }
}