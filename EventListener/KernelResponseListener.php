<?php
namespace Strontium\PjaxBundle\EventListener;

use Strontium\PjaxBundle\VersionGenerator\VersionGeneratorInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class KernelResponseListener
{
    /**
     * @var VersionGeneratorInterface
     */
    protected $versionGenerator;

    public function setVersionGenerator(VersionGeneratorInterface $versionGenerator)
    {
        $this->versionGenerator = $versionGenerator;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function addPjaxVersion(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        if ($request->headers->get('X-PJAX', false)) {
            $response = $event->getResponse();
            $response->headers->set('X-PJAX-Version', $this->versionGenerator->generate($request));
        }
    }
}
