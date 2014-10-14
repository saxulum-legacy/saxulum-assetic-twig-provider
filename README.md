saxulum-assetic-twig-provider
=============================

**works with plain silex-php**

[![Build Status](https://api.travis-ci.org/saxulum/saxulum-assetic-twig-provider.png?branch=master)](https://travis-ci.org/saxulum/saxulum-assetic-twig-provider)
[![Total Downloads](https://poser.pugx.org/saxulum/saxulum-assetic-twig-provider/downloads.png)](https://packagist.org/packages/saxulum/saxulum-assetic-twig-provider)
[![Latest Stable Version](https://poser.pugx.org/saxulum/saxulum-assetic-twig-provider/v/stable.png)](https://packagist.org/packages/saxulum/saxulum-assetic-twig-provider)

Features
--------

* Add assetic support for twig templates

Requirements
------------

 * PHP 5.3+
 * Kriswallsmith's Assets Framework (Assetic) 1.2+
 * Pimple >=2.1,<4
 * Symfony Finder Component 2.3+
 * Twig 1.2+

Installation
------------

Through [Composer](http://getcomposer.org) as [saxulum/saxulum/saxulum-assetic-twig-provider][1].

``` {.php}
$container->register(new TwigServiceProvider());

$container['twig.loader.filesystem'] = $container->extend('twig.loader.filesystem',
    function (\Twig_Loader_Filesystem $twigLoaderFilesystem) {
        $twigLoaderFilesystem->addPath('/path/to/the/views', 'SomeNamespace');

        return $twigLoaderFilesystem;
    }
);

$container->register(new AsseticTwigProvider(), array(
    'assetic.asset.root' => '/path/to/project/root',
    'assetic.asset.asset_root' => '/path/to/asset/root',
));
```

Configuration
-------------

This filter are preconfigured, and active per default:

 * csscopyfile
 * lessphp
 * scssphp
 * cssmin
 * csscompress
 * jsmin

If you want to disable a default filter:

``` {.php}
$container['assetic.filters'] = $container->extend('assetic.filters',
    function ($filters) use ($container) {
        $filters['cssmin'] = false;
        return $filters;
    }
);
```

If you want to add more filters, which aren't preconfigured:

``` {.php}
$container['assetic.filterinstances'] = $container->extend('assetic.filterinstances',
    function ($filterInstances) use ($container) {
        $filterInstances['jsminplus'] = new JSMinPlusFilter();

        return $filterInstances;
    }
);
```

Usage
-----

CSS example

``` {.twig}
{% stylesheets
    'relative/from/path/to/project/root/*.css'
    output='relative/from/path/to/asset/root/css/test.css'
    filter='cssmin'
%}
    {{ asset_url }}
{% endstylesheets %}
```

JS example

``` {.twig}
{% javascripts
    'relative/from/path/to/project/root/*.js'
    output='relative/from/path/to/asset/root/css/test.js'
    filter='jsmin'
%}
    {{ asset_url }}
{% endjavascripts %}
```

Copyright
---------
* Dominik Zogg <dominik.zogg@gmail.com>

[1]: https://packagist.org/packages/saxulum/saxulum-assetic-twig-provider