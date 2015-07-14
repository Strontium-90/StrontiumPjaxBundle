<?php
namespace Strontium\PjaxBundle\Helper;

use Symfony\Component\HttpFoundation\Request;

interface PjaxHelperInterface
{
    /**
     * Was current Request made by PJAX?
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isPjaxRequest(Request $request);

    /**
     * @param Request $request
     *
     * @return string
     */
    public function generateVersion(Request $request);

    /**
     * @return bool
     */
    public function haveGenerator();

    /**
     * Target of PJAX request
     *
     * @param Request $request
     *
     * @return string
     */
    public function getTarget(Request $request);
}
