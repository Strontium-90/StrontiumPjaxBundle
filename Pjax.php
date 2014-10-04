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
}
