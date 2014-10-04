<?php
namespace Strontium\PjaxBundle\EventListener;

use Strontium\PjaxBundle\PjaxInterface;
use Strontium\PjaxBundle\VersionGenerator\VersionGeneratorInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class KernelResponseListener
{
    /**
     * @var PjaxInterface
     */
    protected $pjax;

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
        if ($this->pjax->isPjaxRequest($request)) {
            $response = $event->getResponse();
            $response->headers->set('X-PJAX-Version', $this->pjax->generateVersion($request));
        }
    }
}
