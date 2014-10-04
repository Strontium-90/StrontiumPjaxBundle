<?php
namespace Strontium\PjaxBundle;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class Pjax implements PjaxInterface
{

    /**
     * @var Request
     */
    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function isPjaxRequest()
    {
        return (bool)$this->requestStack->getCurrentRequest()->headers->get('X-PJAX', false);
    }

}
