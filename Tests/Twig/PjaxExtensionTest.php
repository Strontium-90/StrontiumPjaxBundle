<?php
namespace Strontium\PjaxBundle\Tests\Twig;

use Strontium\PjaxBundle\Pjax;
use Strontium\PjaxBundle\Twig\PjaxExtension;

class PjaxExtensionTest extends \Twig_Test_IntegrationTestCase
{
    public function getExtensions()
    {
        $pjax = new Pjax();

        return array(
            new PjaxExtension($pjax),
        );
    }

    public function getFixturesDir()
    {
        return dirname(__FILE__) . '/Fixtures/';
    }
} 