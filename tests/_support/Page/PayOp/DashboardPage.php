<?php

namespace Page\PayOp;

class DashboardPage extends \Page\BasePage
{
    public static $URL = '/en/profile/overview';
    public static $pageTitleBlock = '.page-title';
    public static $overviewText = 'Overview';

    public function amOnThisPage()
    {
        $this->tester->waitForText(self::$overviewText);
//        $this->tester->waitForText('USD');
    }
}