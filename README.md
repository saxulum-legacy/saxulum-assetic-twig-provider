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
 * Kriswallsmith's Assets Framework (Assetic) 1.1+
 * Symfony Finder Component 2.3+
 * Twig 1.2+

Installation
------------

Through [Composer](http://getcomposer.org) as [saxulum/saxulum/saxulum-assetic-twig-provider][1].

``` {.php}
$app->register(new AsseticTwigProvider(), array(
    'assetic.asset.root' => 'path/to/project/root',
    'assetic.asset.asset_root' => 'path/to/asset/root',
));
```

Usage
-----

CSS example

``` {.twig}
{% stylesheets
    'relative/from/path/to/project/root/*.css'
    output='relative/from/path/to/asset/root/css/test.css'
%}
    {{ asset_url }}
{% endstylesheets %}
```

JS example

``` {.twig}
{% javascripts
    'relative/from/path/to/project/root/*.js'
    output='relative/from/path/to/asset/root/css/test.js'
%}
    {{ asset_url }}
{% endjavascripts %}
```

Copyright
---------
* Dominik Zogg <dominik.zogg@gmail.com>

[1]: https://packagist.org/packages/saxulum/saxulum-assetic-twig-provider