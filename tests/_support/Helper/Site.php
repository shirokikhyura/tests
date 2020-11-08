<?php
namespace Helper;

class Site extends \Codeception\Module
{
    const PayOp = 'payop.com';

    private $environmentHosts = [
        'staging' => [
            self::PayOp => 'app.stage.payop.com'
        ],
        'production' => [
            self::PayOp => 'payop.com'
        ]
    ];

    /**
     * @param $site
     * @throws \Codeception\Exception\ModuleException
     */
    public function amOnSite($site)
    {
        $this->getModule('WebDriver')->amOnUrl($site);
    }

    /**
     * @param $site
     * @return string
     */
    public function buildUrl($site)
    {
        $env = $this->_getConfig('env');
        $branch = $this->_getConfig('branch');

        $protocol = $env == 'local' ? 'http' : 'https';
        if ($env == 'staging') {
            $site = $this->environmentHosts['staging'][$site];
        } else if ($env == 'production') {
            $site = $this->environmentHosts['production'][$site];
        }

        return $protocol . '://' . $site;
    }

    /**
     * @return string
     */
    public function seeFullUrl($link)
    {
        $url = $this->getModule('WebDriver')->webDriver->getCurrentURL();
        $pos = strripos($url, $link);

        return \PHPUnit\Framework\Assert::assertTrue(abs($pos) > 0, 'I Not See This '.$link);
    }
}
