<?php

namespace Ui\PayOp;

use Page\PayOp\Ui\DashboardPage;
use Page\PayOp\Ui\LoginPage;
use UiTester;
use Helper\Device;
use Helper\Site;
use Codeception\Example;

class DashboardPageCest
{
    protected function dataProvider()
    {
        return [
            ['device' => Device::DESKTOP],
//            ['device' => Device::TABLET],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function tryToTest(UiTester $I, Example $data)
    {
        $device = $data['device'];
        $I->amOnSite($I->buildUrl(Site::PayOp));

        $loginPage = new LoginPage($I);
        $loginPage->goToThisPage();
        $loginPage->amOnThisPage();
        $loginPage->fillForm('yurii.sh+merchant001@payop.com', 'Qw1qwerty123#');
        $loginPage->submitLoginForm();

        $page = new DashboardPage($I);
        $page->amOnThisPage();
        $page->compareThisPageForDevice($device);
    }
}
