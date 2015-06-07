<?php
namespace Strontium\PjaxBundle\Twig;

use Strontium\PjaxBundle\PjaxInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PjaxExtension extends \Twig_Extension
{

    /**
     * @var PjaxInterface
     */
    protected $pjax;

    /**
     * @var array
     */
    protected $layouts = [];

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param PjaxInterface $pjax
     * @param RequestStack  $requestStack
     */
    public function __construct(PjaxInterface $pjax, RequestStack $requestStack)
    {
        $this->pjax = $pjax;
        $this->requestStack = $requestStack;
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
            'pjax_target'  => new \Twig_Function_Method($this, 'getPjaxTarget', ['is_safe' => ['html']]),
            'pjax_layout'  => new \Twig_Function_Method($this, 'getLayout', ['is_safe' => ['html']]),
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

    public function getLayout()
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$layout = $request->query->get('_layout')) {
            if (isset($this->layouts[$this->pjax->getTarget($request)])){
                return $this->layouts[$this->pjax->getTarget($request)];
            }
        }
        /*

         {% if layout is not defined %}
         {% if pjax_target(app.request) == 'modal' %}
         {% set layout = 'modal' %}
         {% elseif pjax_target(app.request) == 'main' %}
         {% set layout = 'page' %}
         {% endif %}
         {% endif %}
         {% if layout is not defined %}
         {% set layout = is_pjax(app.request) ? 'inline' : 'page' %}
         {% endif %}*/
    }

    /**
     * @param array $layouts
     *
     * @return $this
     */
    public function registerLayouts(array $layouts = array())
    {
        $this->layouts = $layouts;

        return $this;
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
     * @param Request $request
     *
     * @return string
     */
    public function getPjaxTarget(Request $request)
    {
        return $this->pjax->getTarget($request);
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
