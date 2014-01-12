<?php

namespace Saxulum\AsseticTwig\Assetic\Helper;

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
     * @param LazyAssetManager        $lam
     * @param AssetWriter             $aw
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

        foreach ($twigNamespaces as $ns) {
            if (count($this->loader->getPaths($ns)) > 0 ) {
                $iterator = $finder->files()->in($this->loader->getPaths($ns));
                foreach ($iterator as $file) {
                    /** @var SplFileInfo $file */
                    $resource = new TwigResource($this->loader, '@' . $ns . '/' . $file->getRelativePathname());
                    $this->lam->addResource($resource, 'twig');
                }
            }
        }

        foreach ($this->lam->getNames() as $name) {
            $asset = $this->lam->get($name);
            $formula = $this->lam->getFormula($name);

            $debug   = isset($formula[2]['debug'])   ? $formula[2]['debug']   : $this->lam->isDebug();
            $combine = isset($formula[2]['combine']) ? $formula[2]['combine'] : null;

            if (null !== $combine ? !$combine : $debug) {
                foreach ($asset as $leaf) {
                    $this->aw->writeAsset($leaf);
                }
            } else {
                $this->aw->writeAsset($asset);
            }
        }
    }
}
