<?php
namespace Strontium\PjaxBundle\Tests\EventListener;

use Prophecy\PhpUnit\ProphecyTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Strontium\PjaxBundle\EventListener\KernelResponseListener;
use Strontium\PjaxBundle\Helper\PjaxHelperInterface;

class KernelResponseListenerTest extends ProphecyTestCase
{
    /**
     * @var PjaxHelperInterface|ObjectProphecy
     */
    protected $pjax;

    /**
     * @var KernelResponseListener
     */
    protected $listener;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->pjax = $this->prophesize('\Strontium\PjaxBundle\PjaxInterface');
        $this->listener = new KernelResponseListener($this->pjax->reveal());
    }


    public function test_it_should_add_pjax_version_to_response()
    {
        $event = $this->prophesize('Symfony\Component\HttpKernel\Event\FilterResponseEvent');
        $response = $this->prophesize('Symfony\Component\HttpFoundation\Response');

        $event->getResponse()->willReturn($response);
        //$response->

        $this->listener->addPjaxVersion($event->reveal());
    }
}
