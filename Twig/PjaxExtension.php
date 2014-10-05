<?php
namespace Strontium\PjaxBundle\Twig;

use Strontium\PjaxBundle\PjaxInterface;
use Symfony\Component\HttpFoundation\Request;

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
            'is_pjax'      => new \Twig_Function_Method($this, 'isPjax', ['is_safe' => ['html']]),
            'pjax_attr'    => new \Twig_Function_Method($this, 'generatePjaxAttributes', ['is_safe' => ['html']]),
            'pjax_version' => new \Twig_Function_Method($this, 'pjaxVersion', ['is_safe' => ['html']]),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('to_attr', [$this, 'toAttributes'], ['is_safe' => ['html']]),
        );
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function pjaxVersion(Request $request)
    {
        $version = $this->pjax->generateVersion($request);

        return $version ? sprintf('<meta http-equiv="x-pjax-version" content="%s"/>', $version) : null;
    }

    /**
     * Convert array to html attributes
     *
     * @param array $attributes
     *
     * @return string
     */
    public function toAttributes(array $attributes = array())
    {
        $htmlAttr = [];

        foreach ($attributes as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $htmlAttr[] = sprintf(' %s="%s"', $key, $value);
        }

        if (!count($htmlAttr)) {
            return '';
        }

        return implode('', $htmlAttr);
    }

    /**
     * Has Request been made by PJAX?
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isPjax(Request $request)
    {
        return $this->pjax->isPjaxRequest($request);
    }

    /**
     * Generate PJAX attributes
     *
     * @param string  $target             data-pjax-container="$target" where content will load
     * @param string  $redirectTarget     data-pjax-container="$redirectTarget" where content will load after redirect
     * @param boolean $redirectCloseModal should Modal be closed after redirect
     *
     * @return array
     */
    public function generatePjaxAttributes($target = null, $redirectTarget = null, $redirectCloseModal = null)
    {
        $attributes = [];
        if ($target) {
            $attributes['data-pjax'] = (string)$target;
        }
        if ($redirectTarget) {
            $attributes['data-pjax-redirect-target'] = (string)$redirectTarget;
        }
        if ($redirectCloseModal) {
            $attributes['data-pjax-redirect-close-modal'] = $redirectCloseModal;
        }

        return $attributes;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'pjax_extension';
    }
}
