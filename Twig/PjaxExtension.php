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
     * @var array
     */
    protected $frames = [];

    /**
     * @var string
     */
    protected $defaultLayout;

    /**
     * @var RequestStack
     */
    protected $requestStack;

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
            'pjax_frame'   => new \Twig_Function_Method($this, 'getFrame', ['is_safe' => ['html']]),
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
     * @return string
     */
    public function getFrame()
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($this->pjax->isPjaxRequest($request) || null !== $this->requestStack->getParentRequest()) {
            return $this->frames['pjax'];
        }

        return $this->frames['base'];
    }

    /**
     * @return string
     */
    public function getLayout($layout = null)
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($this->pjax->isPjaxRequest($request)) {
            $target = $this->pjax->getTarget($request);
            if (isset($this->layouts[$target])) {
                return $this->layouts[$target];
            } else {
                return $this->layouts[$this->defaultLayout];
            }
        }
        /*if ($layout = $request->query->get('_layout', $layout)) {
            return $this->layouts[$layout];
        }*/

        return $this->layouts[$this->defaultLayout];
    }

    /**
     * @param array $frames
     *
     * @return $this
     */
    public function setFrames(array $frames)
    {
        $this->frames = $frames;

        return $this;
    }

    /**
     * @param array $layouts
     *
     * @return $this
     */
    public function setLayouts(array $layouts)
    {
        $this->layouts = $layouts;

        return $this;
    }

    /**
     * @param string $defaultLayout
     *
     * @return $this
     */
    public function setDefaultLayout($defaultLayout)
    {
        $this->defaultLayout = $defaultLayout;

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
     *
     * @return array
     */
    public function generatePjaxAttributes($target = null, $redirectTarget = null)
    {
        $attributes = [];
        if ($target) {
            $attributes['data-pjax'] = (string)$target;
        }
        if ($redirectTarget) {
            $attributes['data-pjax-redirect-target'] = (string)$redirectTarget;
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
