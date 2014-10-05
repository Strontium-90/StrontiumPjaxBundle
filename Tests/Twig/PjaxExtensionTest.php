<?php
namespace Strontium\PjaxBundle\Tests\Twig;

use Strontium\PjaxBundle\Pjax;
use Strontium\PjaxBundle\Twig\PjaxExtension;

class PjaxExtensionTest extends \Twig_Test_IntegrationTestCase
{
    public function getExtensions()
    {
        $pjax = new Pjax();
        $generator = $this
            ->getMockBuilder('Strontium\PjaxBundle\VersionGenerator\AuthTokenGenerator')
            ->disableOriginalConstructor()
            ->getMock();

        $pjax->setVersionGenerator($generator);
        $generator
            ->method('generate')
            ->will($this->onConsecutiveCalls(1, 2, null, 3));
        return array(
            new PjaxExtension($pjax),
        );
    }

    public function getFixturesDir()
    {
        return dirname(__FILE__) . '/Fixtures/';
    }
} 