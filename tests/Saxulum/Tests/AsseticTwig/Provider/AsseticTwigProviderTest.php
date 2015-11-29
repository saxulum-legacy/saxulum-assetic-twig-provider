<?php

namespace Saxulum\Tests\AsseticTwig\Provider;

use Pimple\Container;
use Psr\Log\NullLogger;
use Saxulum\AsseticTwig\Assetic\Helper\Dumper;
use Saxulum\AsseticTwig\Provider\AsseticTwigProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class AsseticTwigProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testDump()
    {
        $container = new Container();
        $container['debug'] = false;
        $container['charset'] = 'utf-8';

        $logger = $this->getLogger();
        $container['logger'] = $logger;

        $container->register(new TwigServiceProvider());

        $twigPath = $this->getTwigPath();

        $container['twig.loader.filesystem'] = $container->extend('twig.loader.filesystem',
            function (\Twig_Loader_Filesystem $twigLoaderFilesystem) use ($twigPath) {
                $twigLoaderFilesystem->addPath($twigPath, 'SaxulumAsseticTwig');

                return $twigLoaderFilesystem;
            }
        );

        $container->register(new AsseticTwigProvider(), array(
            'assetic.asset.root' => $this->getFixturesPath(),
            'assetic.asset.asset_root' => $this->getAssetPath()
        ));

        /** @var Dumper $dumper */
        $dumper = $container['assetic.asset.dumper'];
        $dumper->dump();

        if(count($logger->entries)) {
            var_dump($logger->entries);
        }

        $this->assertCount(0, $logger->entries);

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
        return __DIR__ . '/../fixtures';
    }

    protected function getExpectsPath()
    {
        return __DIR__ . '/../expects';
    }

    protected function getAssetPath()
    {
        return __DIR__ . '/../../../../../assets';
    }

    protected function getTwigPath()
    {
        return $this->getFixturesPath() . '/twig';
    }

    /**
     * @return NullLogger|\PHPUnit_Framework_MockObject_MockObject|\stdClass
     */
    protected function getLogger()
    {
        /** @var NullLogger|\PHPUnit_Framework_MockObject_MockObject|\stdClass $logger */
        $logger = $this->getMockBuilder(NullLogger::class)
            ->disableOriginalConstructor()
            ->setMethods(['error'])
            ->getMock();

        $logger->entries = array();

        $logger->expects($this->any())
            ->method('error')
            ->will($this->returnCallback(function($message, array $context = array()) use ($logger) {
                $logger->entries[] = array(
                    'level' => 'error',
                    'message' => $message,
                    'context' => $context
                );
            }))
        ;

        return $logger;
    }
}
