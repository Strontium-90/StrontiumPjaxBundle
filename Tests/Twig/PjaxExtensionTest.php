<?php
namespace Strontium\PjaxBundle\Tests\Twig;

use Strontium\PjaxBundle\Twig\PjaxExtension;

class PjaxExtensionTest extends \Twig_Test_IntegrationTestCase
{
    public function getExtensions()
    {
        return array(
            new PjaxExtension(),
        );
    }

    public function getFixturesDir()
    {
        return dirname(__FILE__).'/Fixtures/';
    }
} 