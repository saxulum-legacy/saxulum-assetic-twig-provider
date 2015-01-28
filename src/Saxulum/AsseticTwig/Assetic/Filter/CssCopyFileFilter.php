<?php

namespace Saxulum\AsseticTwig\Assetic\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\BaseCssFilter;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

class CssCopyFileFilter extends BaseCssFilter
{
    /**
     * @var string
     */
    protected $assetRoot;

    /**
     * @param string $assetRoot
     */
    public function __construct($assetRoot)
    {
        $this->assetRoot = $assetRoot;
    }

    /**
     * @param AssetInterface $asset
     */
    public function filterLoad(AssetInterface $asset)
    {
    }

    /**
     * @param AssetInterface $asset
     */
    public function filterDump(AssetInterface $asset)
    {
        $sourceBase = $asset->getSourceRoot();
        $sourcePath = $asset->getSourcePath();
        $assetRoot = $this->assetRoot;
        if (null === $sourcePath) {
            return;
        }
        $content = $this->filterReferences($asset->getContent(), function ($matches) use ($sourceBase, $sourcePath, $assetRoot) {
            // its not a relative path
            if (false !== strpos($matches['url'], '://') ||
                0 === strpos($matches['url'], '//') ||
                0 === strpos($matches['url'], 'data:') ||
                (isset($matches['url'][0]) && '/' == $matches['url'][0])) {
                return $matches[0];
            }

            $url = $matches['url'];

            if (false !== $pos = strpos($url, '?')) {
                $url = substr($url, 0, $pos);
            }

            $sourceAsset = dirname($sourceBase.'/'.$sourcePath).'/'.$url;
  
            if (!is_file($sourceAsset)) {
                return $matches[0];
            }

            $mimeType = MimeTypeGuesser::getInstance()->guess($sourceAsset);
            $destRelativePath = substr($mimeType, 0, strpos($mimeType, '/')).'/'.basename($url);
            $destAsset = $assetRoot.'/'.$destRelativePath;

            if (!is_dir(dirname($destAsset))) {
                mkdir(dirname($destAsset), 0777, true);
            }

            copy($sourceAsset, $destAsset);

            return str_replace($matches['url'], '../'.$destRelativePath, $matches[0]);
        });
        $asset->setContent($content);
    }
}
