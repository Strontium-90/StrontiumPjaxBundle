<?php
namespace Strontium\PjaxBundle\EventListener;

use Strontium\PjaxBundle\PjaxInterface;
use Strontium\PjaxBundle\VersionGenerator\VersionGeneratorInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class KernelResponseListener
{
    /**
     * @var PjaxInterface
     */
    protected $pjax;

    /**
     * @param PjaxInterface $pjax
     */
    public function __construct(PjaxInterface $pjax)
    {
        $this->pjax = $pjax;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function addPjaxVersion(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        if ($this->pjax->isPjaxRequest($request) && $this->pjax->haveGenerator()) {
            $response = $event->getResponse();
            $response->headers->set('X-PJAX-Version', $this->pjax->generateVersion($request));
        }
    }

    /**
     * @param FilterResponseEvent $event
     *
     * @return Response
     */
    public function pjaxRedirect(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if ($this->pjax->isPjaxRequest($request) && $response->isRedirect()) {
            $redirectCookieName = sprintf('pjax_redirect_%s', $request->headers->get('X-PJAX-Target'));
            $redirectTo = $response->headers->get('Location');
            $response->headers->setCookie(
                new Cookie(rawurlencode($redirectCookieName), $redirectTo, 0, '/', null, false, false)
            );
        }

        return $response;
    }
}
