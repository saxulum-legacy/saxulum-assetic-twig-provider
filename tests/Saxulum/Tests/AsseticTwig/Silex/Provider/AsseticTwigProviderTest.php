<?php

namespace Saxulum\Tests\AsseticTwig\Silex\Provider;

use Saxulum\AsseticTwig\Silex\Provider\AsseticTwigProvider;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;

class AsseticTwigProviderTest extends \PHPUnit_Framework_TestCase
{
    const ROOT = './environment';
    const CSS_FILENAME = 'test.css';
    const CSS_CONTENT = 'body { margin: 0; padding: 0; }';
    const TWIG_FILENAME = 'test.html.twig';
    const TWIG_CONTENT = "{% stylesheets 'css/test.css' output='css/test.css' %}{{ asset_url }}{% endstylesheets %}";

    public function testDump()
    {
        $app = new Application();
        $app['debug'] = true;

        $app->register(new TwigServiceProvider());

        $twigPath = $this->getTwigPath();

        $app['twig.loader.filesystem'] = $app->share($app->extend('twig.loader.filesystem',
            function (\Twig_Loader_Filesystem $twigLoaderFilesystem) use ($twigPath) {
                $twigLoaderFilesystem->addPath($twigPath, 'SaxulumAsseticTwig');

                return $twigLoaderFilesystem;
            }
        ));

        $app->register(new AsseticTwigProvider(), array(
            'assetic.asset.root' => self::ROOT,
            'assetic.asset.asset_root' => $this->getAssetPath()
        ));

        $app['assetic.asset.dumper']->dump();

        $this->assertFileExists($this->getAssetCssFilepath());
        $this->assertEquals(self::CSS_CONTENT, file_get_contents($this->getAssetCssFilepath()));
    }

    public function setUp()
    {
        if (is_dir(self::ROOT)) {
            $this->tearDown();
        }

        mkdir(self::ROOT);
        mkdir($this->getAssetPath());
        mkdir($this->getCssPath());
        mkdir($this->getTwigPath());

        file_put_contents($this->getCssFilepath(), self::CSS_CONTENT);
        file_put_contents($this->getTwigFilepath(), self::TWIG_CONTENT);
    }

    public function tearDown()
    {
        unlink($this->getAssetCssFilepath());
        unlink($this->getCssFilepath());
        unlink($this->getTwigFilepath());

        rmdir($this->getAssetCssPath());
        rmdir($this->getAssetPath());
        rmdir($this->getCssPath());
        rmdir($this->getTwigPath());
        rmdir(self::ROOT);
    }

    protected function getAssetPath()
    {
        return self::ROOT. '/assets';
    }

    protected function getAssetCssPath()
    {
        return $this->getAssetPath() . '/css';
    }

    protected function getCssPath()
    {
        return self::ROOT. '/css';
    }

    protected function getTwigPath()
    {
        return self::ROOT. '/views';
    }

    protected function getAssetCssFilepath()
    {
        return $this->getAssetCssPath() . '/' . self::CSS_FILENAME;
    }

    protected function getCssFilepath()
    {
        return $this->getCssPath() . '/' . self::CSS_FILENAME;
    }

    protected function getTwigFilepath()
    {
        return $this->getTwigPath() . '/' . self::TWIG_FILENAME;
    }
}
