<?php

namespace Page;

use Helper\Device;
use UiTester;

class BaseUiPage
{
    /**
     * @var UiTester
     */
    protected $tester;

    public static $URL = '';

    public function __construct(UiTester $I)
    {
        $this->tester = $I;
    }

    public function acceptNDACookie()
    {
        $this->tester->setCookie('cookieAccess', 'access');
    }

    public function amOnThisPage()
    {
        $I = $this->tester;
        $I->amOnPage(static::$URL);
        $I->waitForPageLoaded();
    }

    public function clickElement($buttonSelector)
    {
        $I = $this->tester;
        $I->waitForElementVisible($buttonSelector);
        $I->click($buttonSelector);
        $I->waitForAjaxStopped();
    }

    public function goToThisPage()
    {
        $this->tester->amOnPage(static::$URL);
    }

    public function comparePageForDevice(int $device, array $exclude = [], int $wait = 1)
    {
        $this->compareElementForDevice('body', 'body', $device, $exclude, $wait);
    }

    public function compareElementForDevice(
        string $elementId,
        string $elementSelector,
        int $device,
        array $excludeElements = [],
        int $wait = 0
    ) {
        $I = $this->tester;
        $I->amOnDevice($device);
        $I->waitForElementVisible($elementSelector);
        $I->hideElementsForScreenshot($excludeElements);
        $I->scrollTo($elementSelector);
        $I->wait($wait);
        $elementId .= '-' . Device::getName($device);
        $I->dontSeeVisualChanges($elementId, $elementSelector, $excludeElements, 1.0e-6);
    }
}
