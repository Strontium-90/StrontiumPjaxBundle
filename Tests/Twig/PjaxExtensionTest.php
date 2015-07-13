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
        $generator
            ->method('generate')
            ->will($this->onConsecutiveCalls(1, 2, null, 3));

        $requestStack = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')
            ->disableOriginalConstructor()
            ->getMock();

        $pjax->setVersionGenerator($generator);

        return array(
            new PjaxExtension($pjax, $requestStack),
        );
    }

    public function getFixturesDir()
    {
        return dirname(__FILE__) . '/Fixtures/';
    }
}
