<?php
namespace Strontium\PjaxBundle\VersionGenerator;

use Symfony\Component\HttpFoundation\Request;

interface VersionGeneratorInterface
{
    /**
     * @param Request $request
     *
     * @return string
     */
    public function generate(Request $request);
}
