StrontiumPjaxBundle
===================
This bundle provide integration of [PJAX](https://github.com/defunkt/jquery-pjax) into [Symfony 2](https://github.com/symfony/symfony) framework.

[![Build Status](https://travis-ci.org/Strontium-90/StrontiumPjaxBundle.svg?branch=master)](https://travis-ci.org/Strontium-90/StrontiumPjaxBundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Strontium-90/StrontiumPjaxBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Strontium-90/StrontiumPjaxBundle/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/914e7f8c-12b8-4c19-b6f7-e417cd680a66/mini.png)](https://insight.sensiolabs.com/projects/914e7f8c-12b8-4c19-b6f7-e417cd680a66)

Installation
------------

`composer require strontium/pjax-bundle`

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
