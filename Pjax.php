<?php
namespace Strontium\PjaxBundle;

use Strontium\PjaxBundle\VersionGenerator\VersionGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;

class Pjax implements PjaxInterface
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
        return $this->versionGenerator !== null;
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

        if (preg_match('/\[data-pjax-container="([^"]+)"\]/', $target, $m)) {
            return $m[1];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRealTarget(Request $request)
    {
        $target = $this->getTarget($request);
        if ($request->cookies->get(sprintf('pjax_redirect_%s', $target))) {
            return $request->headers->get('X-PJAX-Redirect-Target');
        }

        return $this->getTarget($request);
        /*app.request.headers.get('X-PJAX-Container') == '[data-pjax-container="modal"]'
        and not app.request.cookies.get('pjax_redirect_modal')
    or app.request.query.get('_pjax') == '[data-pjax-container="modal"]'

    app.request.headers.get('X-PJAX-Container') == '[data-pjax-container="main"]'
    or app.request.query.get('_pjax') == '[data-pjax-container="main"]'
    or app.request.cookies.get('pjax_redirect_modal')
    and app.request.headers.get('') == 'main'*/
    }
}
