<?php

namespace PayOp;

use Page\PayOp\DashboardPage;
use AcceptanceTester;
use Helper\Site;
use Page\PayOp\LoginPage;

class LoginFromHomePageCest
{
    public function tryToTest(AcceptanceTester $I)
    {
        $I->amOnSite($I->buildUrl(Site::PayOp));

        $loginPage = new LoginPage($I);
        $loginPage->goToThisPage();
        $loginPage->amOnThisPage();
        $loginPage->fillForm('yurii.sh+merchant001@payop.com', 'Qw1qwerty123#');
        $loginPage->submitLoginForm();

        $dashboardPage = new DashboardPage($I);
        $dashboardPage->amOnThisPage();
    }
}