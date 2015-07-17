<?php
namespace Strontium\PjaxBundle\Twig;

use Strontium\PjaxBundle\Helper\PjaxHelperInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PjaxExtension extends \Twig_Extension
{

    /**
     * @var PjaxHelperInterface
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
    protected $defaultFrame;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @param PjaxHelperInterface $pjax
     * @param RequestStack        $requestStack
     */
    public function __construct(PjaxHelperInterface $pjax, RequestStack $requestStack)
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
            new \Twig_SimpleFunction('is_pjax', [$this, 'isPjax'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('pjax_attr', [$this, 'generatePjaxAttributes'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('pjax_version', [$this, 'pjaxVersion'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('pjax_target', [$this, 'getPjaxTarget'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('pjax_layout', [$this, 'getLayout'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('pjax_frame', [$this, 'getFrame'], ['is_safe' => ['html']]),
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
     * @param string $frame
     * @return mixed
     */
    public function getFrame($frame = null)
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null !== $frame) {
            return $this->frames[$frame];
        }
        if ($this->pjax->isPjaxRequest($request)) {
            $target = $this->pjax->getTarget($request);
            if (isset($this->frames[$target])) {
                return $this->frames[$target];
            } else {
                return $this->frames[$this->defaultFrame];
            }
        }

        return $this->frames[$this->defaultFrame];
    }

    /**
     * @return string
     */
    public function getLayout($layout = 'base')
    {
        $request = $this->requestStack->getCurrentRequest();
        if ((null !== $request && $this->pjax->isPjaxRequest($request))
            || null !== $this->requestStack->getParentRequest()
        ) {
            return $this->layouts['pjax'];
        }

        return $this->layouts[$layout];
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
     * @param string $defaultFrame
     *
     * @return $this
     */
    public function setDefaultFrame($defaultFrame)
    {
        $this->defaultFrame = $defaultFrame;

        return $this;
    }

    /**
     * @param Request $request
     *
     * @return string|null
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
     * @param string $target         data-pjax-container="$target" where content will load
     * @param string $redirectTarget data-pjax-redirect-target="$redirectTarget" where content will load after redirect
     *
     * @return array
     */
    public function generatePjaxAttributes($target = null, $redirectTarget = null)
    {
        $attributes = [];
        if (null !== $target) {
            $attributes['data-pjax'] = (string)$target;
        }
        if (null !== $redirectTarget) {
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
