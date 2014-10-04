StrontiumPjaxBundle
===================


Installation
------------

``` json
{
    "require": {
        "mopa/bootstrap-bundle": "v3.0.0-beta4",
        "twbs/bootstrap": "v3.2.0"
    }
}
```

To start using the bundle, register it in your Kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Strontium\PjaxBundle\StrontiumPjaxBundle(),
    );
    // ...
}
```
 
Documentation
-------------



Live Show
---------






License
-------

This bundle is under the MIT license.
