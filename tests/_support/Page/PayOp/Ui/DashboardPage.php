<?php

namespace Page\PayOp\Ui;

class DashboardPage extends \Page\BaseUiPage
{
    public static $URL = '/en/profile/overview';

    public function amOnThisPage()
    {
        $this->tester->waitForText('USD');
    }

    public function compareThisPageForDevice($device)
    {
        $this->comparePageForDevice($device, ['.clock'], 5);
    }
}