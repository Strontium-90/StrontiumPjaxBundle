<?php
namespace Strontium\PjaxBundle;

interface PjaxInterface
{
    /**
     * Was current Request made by PJAX?
     * @return bool
     */
    public function isPjaxRequest();
}