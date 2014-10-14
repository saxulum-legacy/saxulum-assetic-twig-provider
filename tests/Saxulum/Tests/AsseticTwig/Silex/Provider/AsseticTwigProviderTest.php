<?php

namespace Saxulum\Tests\AsseticTwig\Silex\Provider;

use Saxulum\AsseticTwig\Silex\Provider\AsseticTwigProvider;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class AsseticTwigProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testDump()
    {
        $app = new Application();
        $app['debug'] = false;

        $app->register(new TwigServiceProvider());

        $twigPath = $this->getTwigPath();

        $app['twig.loader.filesystem'] = $app->share($app->extend('twig.loader.filesystem',
            function (\Twig_Loader_Filesystem $twigLoaderFilesystem) use ($twigPath) {
                $twigLoaderFilesystem->addPath($twigPath, 'SaxulumAsseticTwig');

                return $twigLoaderFilesystem;
            }
        ));

        $app->register(new AsseticTwigProvider(), array(
            'assetic.asset.root' => $this->getFixturesPath(),
            'assetic.asset.asset_root' => $this->getAssetPath()
        ));

        $app['assetic.asset.dumper']->dump();

        $this->fileComparsion('css/test-copyfile.css');
        $this->fileComparsion('image/test.png');
        $this->fileComparsion('css/test-less.css');
        $this->fileComparsion('css/test-scss.css');
        $this->fileComparsion('css/test-cssmin.css');
        $this->fileComparsion('css/test-csscompress.css');
        $this->fileComparsion('js/test.js');
    }

    protected function fileComparsion($path)
    {
        $this->assertFileExists($this->getAssetPath() . '/' . $path);
        $this->assertEquals(
            file_get_contents($this->getExpectsPath() . '/' . $path),
            file_get_contents($this->getAssetPath() . '/' . $path)
        );
    }

    protected function tearDown()
    {
        $files = $this->getFiles($this->getAssetPath());
        foreach ($files as $file) {
            unlink($file);
        }

        $directories = $this->getDirectories($this->getAssetPath());
        foreach ($directories as $directory) {
            rmdir($directory);
        }

        rmdir($this->getAssetPath());
    }

    protected function getFiles($path)
    {
        $filePaths = array();

        $finder = new Finder();
        $iterator = $finder->files()->in($path);

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            $filePaths[] = $file->getPathname();
        }

        return $filePaths;
    }

    protected function getDirectories($path)
    {
        $directoryPaths = array();

        $finder = new Finder();
        $iterator = $finder->directories()->in($path);
        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            $directoryPaths[] = $file->getPathname();
        }

        return $directoryPaths;
    }

    protected function getFixturesPath()
    {
        return __DIR__ . '/../../fixtures';
    }

    protected function getExpectsPath()
    {
        return __DIR__ . '/../../expects';
    }

    protected function getAssetPath()
    {
        return __DIR__ . '/../../../../../../assets';
    }

    protected function getTwigPath()
    {
        return $this->getFixturesPath() . '/twig';
    }
}
