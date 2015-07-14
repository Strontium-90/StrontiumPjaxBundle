<?php
namespace Strontium\PjaxBundle\Helper;

use Strontium\PjaxBundle\VersionGenerator\VersionGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;

class PjaxHelper implements PjaxHelperInterface
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
     * {@inheritdoc}
     */
    public function isPjaxRequest(Request $request)
    {
        return (bool)$request->headers->get('X-PJAX', false);
    }

    /**
     * {@inheritdoc}
     */
    public function generateVersion(Request $request)
    {
        return $this->haveGenerator() ? $this->versionGenerator->generate($request) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function haveGenerator()
    {
        return null !== $this->versionGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getTarget(Request $request)
    {
        if (!$this->isPjaxRequest($request)) {
            return null;
        }
        $target = $request->query->get('_pjax', $request->headers->get('X-PJAX-Container'));

        if (!preg_match('/\[data-pjax-container="([^"]+)"\]/', $target, $m)) {
            return null;
        }
        $target = $m[1];

        $redirectTarget = $request->headers->get('X-PJAX-Redirect-Target');
        if ($redirectTarget && $request->cookies->get(sprintf('pjax_redirect_%s', $target))) {
            return $redirectTarget;
        }

        return $target;
    }
}
