<?php

namespace Saxulum\Tests\AsseticTwig\Silex\Provider;

use Saxulum\AsseticTwig\Silex\Provider\AsseticTwigProvider;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;

class AsseticTwigProviderTest extends \PHPUnit_Framework_TestCase
{
    const ROOT = './environment';

    const CSS_FILENAME = 'test.css';
    const CSS_CONTENT = <<<EOT
body {
    margin: 0;
    padding: 0;
    background-image: url(../image/test.png);
}
EOT;
    const CSS_ASSET_FILENAME = 'test.css';
    const CSS_ASSET_CONTENT = 'body{margin:0;padding:0;background-image:url(../image/test.png)}';

    const IMAGE_FILENAME = 'test.png';
    const IMAGE_BASE64_CONTENT = 'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3gEMDR4D1sa8wQAAABl0RVh0Q29tbWVudABDcmVhdGVkIHdpdGggR0lNUFeBDhcAAABCSURBVBjThZAxDsAwCMRs8v8304Eo7VDIDUjowIdQBYDMBKqtGhGLXq19kPFrVxBgR97xjLrYDmT1tl2D76mft6gPMgcJN1bVIdYAAAAASUVORK5CYII=';

    const LESS_FILENAME = 'test.less';
    const LESS_CONTENT = <<<EOT
@bgcolor: #FF0000;

body {
    background-color: @bgcolor;
}
EOT;
    const LESS_ASSET_FILENAME = 'test-less.css';
    const LESS_ASSET_CONTENT = 'body{background-color:#FF0000}';

    const SCSS_FILENAME = 'test.scss';
    const SCSS_CONTENT = <<<EOT
\$color: #000000;

body {
    color: \$color;
}
EOT;
    const SCSS_ASSET_FILENAME = 'test-scss.css';
    const SCSS_ASSET_CONTENT = 'body{color:#000}';

    const JS_FILENAME = 'test.js';
    const JS_CONTENT = <<<EOT
jQuery(document).ready(function () {
    console.log(jQuery);
});
EOT;
    const JS_ASSET_FILENAME = 'test.js';
    const JS_ASSET_CONTENT = 'jQuery(document).ready(function(){console.log(jQuery);});';

    const TWIG_FILENAME = 'test.html.twig';
    const TWIG_CONTENT = <<<EOT
{% stylesheets 'css/test.css' filter='csscopyfile,cssmin' output='css/test.css' %}
    {{ asset_url }}
{% endstylesheets %}
{% stylesheets 'less/test.less' filter='lessphp,cssmin' output='css/test-less.css' %}
    {{ asset_url }}
{% endstylesheets %}
{% stylesheets 'scss/test.scss' filter='scssphp,cssmin' output='css/test-scss.css' %}
    {{ asset_url }}
{% endstylesheets %}
{% javascripts 'js/test.js' filter='jsmin' output='js/test.js' %}
    {{ asset_url }}
{% endjavascripts %}
EOT;

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
        $this->assertEquals(self::CSS_ASSET_CONTENT, file_get_contents($this->getAssetCssFilepath()));

        $this->assertFileExists($this->getAssetImageFilepath());
        $this->assertEquals(self::IMAGE_BASE64_CONTENT, base64_encode(file_get_contents($this->getAssetImageFilepath())));

        $this->assertFileExists($this->getAssetLessFilepath());
        $this->assertEquals(self::LESS_ASSET_CONTENT, file_get_contents($this->getAssetLessFilepath()));

        $this->assertFileExists($this->getAssetScssFilepath());
        $this->assertEquals(self::SCSS_ASSET_CONTENT, file_get_contents($this->getAssetScssFilepath()));

        $this->assertFileExists($this->getAssetJsFilepath());
        $this->assertEquals(self::JS_ASSET_CONTENT, file_get_contents($this->getAssetJsFilepath()));
    }

    public function setUp()
    {
        if (is_dir(self::ROOT)) {
            $this->tearDown();
        }

        mkdir(self::ROOT);
        mkdir($this->getAssetPath());
        mkdir($this->getCssPath());
        mkdir($this->getLessPath());
        mkdir($this->getScssPath());
        mkdir($this->getImagePath());
        mkdir($this->getJsPath());
        mkdir($this->getTwigPath());

        file_put_contents($this->getCssFilepath(), self::CSS_CONTENT);
        file_put_contents($this->getLessFilepath(), self::LESS_CONTENT);
        file_put_contents($this->getScssFilepath(), self::SCSS_CONTENT);
        file_put_contents($this->getImageFilepath(), base64_decode(self::IMAGE_BASE64_CONTENT));
        file_put_contents($this->getJsFilepath(), self::JS_CONTENT);
        file_put_contents($this->getTwigFilepath(), self::TWIG_CONTENT);
    }

    public function tearDown()
    {
        unlink($this->getAssetCssFilepath());
        unlink($this->getAssetLessFilepath());
        unlink($this->getAssetScssFilepath());
        unlink($this->getAssetImageFilepath());
        unlink($this->getAssetJsFilepath());
        unlink($this->getCssFilepath());
        unlink($this->getLessFilepath());
        unlink($this->getScssFilepath());
        unlink($this->getImageFilepath());
        unlink($this->getJsFilepath());
        unlink($this->getTwigFilepath());

        rmdir($this->getAssetCssPath());
        rmdir($this->getAssetImagePath());
        rmdir($this->getAssetJsPath());
        rmdir($this->getAssetPath());
        rmdir($this->getCssPath());
        rmdir($this->getLessPath());
        rmdir($this->getScssPath());
        rmdir($this->getImagePath());
        rmdir($this->getJsPath());
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

    protected function getAssetImagePath()
    {
        return $this->getAssetPath() . '/image';
    }

    protected function getAssetJsPath()
    {
        return $this->getAssetPath() . '/js';
    }

    protected function getCssPath()
    {
        return self::ROOT. '/css';
    }

    protected function getLessPath()
    {
        return self::ROOT. '/less';
    }

    protected function getScssPath()
    {
        return self::ROOT. '/scss';
    }

    protected function getImagePath()
    {
        return self::ROOT. '/image';
    }

    protected function getJsPath()
    {
        return self::ROOT. '/js';
    }

    protected function getTwigPath()
    {
        return self::ROOT. '/views';
    }

    protected function getAssetCssFilepath()
    {
        return $this->getAssetCssPath() . '/' . self::CSS_ASSET_FILENAME;
    }

    protected function getAssetLessFilepath()
    {
        return $this->getAssetCssPath() . '/' . self::LESS_ASSET_FILENAME;
    }

    protected function getAssetScssFilepath()
    {
        return $this->getAssetCssPath() . '/' . self::SCSS_ASSET_FILENAME;
    }

    protected function getAssetImageFilepath()
    {
        return $this->getAssetImagePath() . '/' . self::IMAGE_FILENAME;
    }

    protected function getAssetJsFilepath()
    {
        return $this->getAssetJsPath() . '/' . self::JS_ASSET_FILENAME;
    }

    protected function getCssFilepath()
    {
        return $this->getCssPath() . '/' . self::CSS_FILENAME;
    }

    protected function getLessFilepath()
    {
        return $this->getLessPath() . '/' . self::LESS_FILENAME;
    }

    protected function getScssFilepath()
    {
        return $this->getScssPath() . '/' . self::SCSS_FILENAME;
    }

    protected function getImageFilepath()
    {
        return $this->getImagePath() . '/' . self::IMAGE_FILENAME;
    }

    protected function getJsFilepath()
    {
        return $this->getJsPath() . '/' . self::JS_FILENAME;
    }

    protected function getTwigFilepath()
    {
        return $this->getTwigPath() . '/' . self::TWIG_FILENAME;
    }
}
