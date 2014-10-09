<?php
namespace Strontium\PjaxBundle;

use Symfony\Component\HttpFoundation\Request;

interface PjaxInterface
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
     * Real target of PJAX request
     *
     * @param Request $request
     *
     * @return string
     */
    public function getTarget(Request $request);

    /**
     * Real target of PJAX request
     *
     * @param Request $request
     *
     * @return string
     */
    public function getRealTarget(Request $request);
}
