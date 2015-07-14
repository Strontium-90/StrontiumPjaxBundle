<?php
namespace Strontium\PjaxBundle\Tests\EventListener;

use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Strontium\PjaxBundle\EventListener\KernelResponseListener;
use Strontium\PjaxBundle\Helper\PjaxHelperInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

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
        $this->pjax = $this->prophesize('\Strontium\PjaxBundle\Helper\PjaxHelperInterface');
        $this->listener = new KernelResponseListener($this->pjax->reveal());
    }


    public function test_it_should_add_pjax_version_to_response()
    {
        $event = $this->prophesize('Symfony\Component\HttpKernel\Event\FilterResponseEvent');
        $response = $this->prophesize('Symfony\Component\HttpFoundation\Response');
        $request = $this->prophesize('\Symfony\Component\HttpFoundation\Request');
        $headers = $this->prophesize('\Symfony\Component\HttpFoundation\ResponseHeaderBag');
        $response->headers = $headers->reveal();

        $event->getRequest()->willReturn($request->reveal());
        $event->getResponse()->willReturn($response->reveal());

        $this->pjax->isPjaxRequest($request->reveal())->willReturn(true);
        $this->pjax->haveGenerator()->willReturn(true);

        $this->pjax->generateVersion($request)->willReturn('version');
        $headers->set('X-PJAX-Version', 'version')->shouldBeCalled();

        $this->listener->addPjaxVersion($event->reveal());
    }

    public function test_it_should_skip_if_its_not_pjax_redirect()
    {
        $event = $this->prophesize('Symfony\Component\HttpKernel\Event\FilterResponseEvent');

        $request = $this->prophesize('\Symfony\Component\HttpFoundation\Request');
        $requestHeaders = $this->prophesize('\Symfony\Component\HttpFoundation\HeaderBag');
        $request->headers = $requestHeaders->reveal();
        $response = $this->prophesize('Symfony\Component\HttpFoundation\Response');
        $headers = $this->prophesize('\Symfony\Component\HttpFoundation\ResponseHeaderBag');
        $response->headers = $headers->reveal();
        $event->getRequest()->willReturn($request->reveal());
        $event->getResponse()->willReturn($response->reveal());


        $this->pjax->isPjaxRequest($request->reveal())->willReturn(false);
        $headers->setCookie()->shouldNotBeCalled();


        $this->listener->pjaxRedirect($event->reveal());
    }

    public function test_it_should_add_cookie_on_redirect()
    {
        $event = $this->prophesize('Symfony\Component\HttpKernel\Event\FilterResponseEvent');

        $request = $this->prophesize('\Symfony\Component\HttpFoundation\Request');
        $requestHeaders = $this->prophesize('\Symfony\Component\HttpFoundation\HeaderBag');
        $request->headers = $requestHeaders->reveal();
        $response = $this->prophesize('Symfony\Component\HttpFoundation\Response');
        $headers = $this->prophesize('\Symfony\Component\HttpFoundation\ResponseHeaderBag');
        $response->headers = $headers->reveal();
        $event->getRequest()->willReturn($request->reveal());
        $event->getResponse()->willReturn($response->reveal());


        $this->pjax->isPjaxRequest($request->reveal())->willReturn(true);
        $response->isRedirect()->wilLReturn(true);

        $headers->get('Location')->willReturn('redirect_uri');
        $headers->setCookie(Argument::any())->shouldBeCalled();

        $this->listener->pjaxRedirect($event->reveal());
    }
}
