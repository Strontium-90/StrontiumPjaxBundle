<?php
namespace Strontium\PjaxBundle\Tests;

use Strontium\PjaxBundle\Pjax;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;

class PjaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider headersProvider
     */
    public function testIsPjaxRequest($isPjax, array $headers)
    {
        $pjax = new Pjax();


        $request = $this->getMock('\Symfony\Component\HttpFoundation\Request', array());
        $request->headers = new HeaderBag($headers);

        $this->assertEquals($isPjax, $pjax->isPjaxRequest($request));
    }

    public function headersProvider()
    {
        return array(
            array(
                false,
                [
                    'X-PJAX' => false,
                ]
            ),
            array(
                false,
                []
            ),
            array(
                false,
                [
                    'ololo' => true,
                ]
            ),
            array(
                true,
                [
                    'X-PJAX' => true,
                ]
            ),
        );
    }
}
