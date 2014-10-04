<?php
namespace Strontium\PjaxBundle\Twig;

use Strontium\PjaxBundle\PjaxInterface;

class PjaxExtension extends \Twig_Extension
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
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(

            'is_pjax' => new \Twig_Function_Method($this, 'isPjax', ['is_safe' => ['html']]),
        );
    }


    /**
     * Was current Request made by PJAX?
     *
     * @return bool
     */
    public function isPjax()
    {
        return $this->pjax->isPjaxRequest();
    }


    public function getName()
    {
        return 'misc_extension';
    }
} 