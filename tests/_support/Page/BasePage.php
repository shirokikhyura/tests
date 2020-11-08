<?php
namespace Page;

class BasePage
{
    /**
     * @var \AcceptanceTester
     */
    protected $tester;

    public static $URL = '';

    public static $cookieButton = '.cookie__accept-btn';

    public function __construct(\AcceptanceTester $I)
    {
        $this->tester = $I;
    }

    public function acceptNDACookie()
    {
        $this->tester->setCookie('cookieAccess', 'access');
//        $this->tester->click(self::$cookieButton);
//        $this->tester->click('#accept_cookie_window_close');
    }

    public function waitForReloadForm($timeout = 60)
    {
        $I = $this->tester;
        $I->waitForJS("if (window.jQuery) return jQuery.active == 0; else return true;", $timeout);
    }

    public function waitForPageLoaded($timeout = 60)
    {
        $I = $this->tester;
        $I->waitForJS(
            'return document.readyState == "complete"',
            $timeout
        );
        $this->waitForReloadForm();
    }

    public function fillField($fieldSelector, $value)
    {
        $I = $this->tester;
        $I->waitForElementVisible($fieldSelector);
        $I->fillField($fieldSelector, $value);
        $this->waitForReloadForm();
    }

    public function clickElement($buttonSelector)
    {
        $I = $this->tester;
        $I->waitForElementVisible($buttonSelector);
        $I->click($buttonSelector);
        $this->waitForReloadForm();
    }

    public function goToThisPage()
    {
        $this->tester->amOnPage(static::$URL);
    }

    public function seeIamOnThisPage($option = 0)
    {
        $this->waitForPageLoaded();
        $this->tester->seeInCurrentUrl(static::$URL);
    }

    public function seeSelectedValueIs($fieldSelector, $expectedValue)
    {
        $I = $this->tester;
        $I->waitForElementVisible($fieldSelector);
        $I->seeOptionIsSelected($fieldSelector, $expectedValue);
    }

    public function seeFieldValueIs($fieldSelector, $expectedValue, $delta = 0.15)
    {
        $I = $this->tester;
        $I->waitForElementVisible($fieldSelector);
        $actualValue = $I->grabTextFrom($fieldSelector);
        $I->assertStringFloatValues($expectedValue, $actualValue, $delta);
    }

    public function seeFieldTextIs($fieldSelector, $expectedValue)
    {
        $I = $this->tester;
        $I->waitForElementVisible($fieldSelector);
        $I->see($expectedValue, $fieldSelector);
    }

    public function seeInputValueIs($fieldSelector, $expectedValue)
    {
        $I = $this->tester;
        $I->waitForElementVisible($fieldSelector);
        $I->seeInField($fieldSelector, $expectedValue);
    }

    public function getFieldValue($fieldSelector, $position = -1)
    {
        $I = $this->tester;
        $I->waitForElementVisible($fieldSelector);
        $value = $I->grabTextFrom($fieldSelector);
        return $I->getFloatValueFromString($value, $position);
    }

    public function getFieldText($fieldSelector)
    {
        $I = $this->tester;
        $I->waitForElementVisible($fieldSelector);
        return $I->grabTextFrom($fieldSelector);
    }

    public function getHiddenFieldValue($fieldSelector)
    {
        $I = $this->tester;
        $I->waitForElement($fieldSelector);
        $value = $I->executeJS("return $('$fieldSelector').text()");
        return $I->getFloatValueFromString($value);
    }

    public function selectOption($fieldSelector, $optionToSelect)
    {
        $I = $this->tester;
        $I->waitForElementVisible($fieldSelector);
        $I->selectOption($fieldSelector, $optionToSelect);
        $this->waitForReloadForm();
    }

    public function checkCheckbox($selector)
    {
        $I = $this->tester;
        $I->waitForElementVisible($selector);
        $I->checkOption($selector);
        $this->waitForReloadForm();
    }

    public function selectDropdownListOption($option, $contextSelector)
    {
        $I = $this->tester;
        $I->waitForElementVisible($contextSelector);
        $I->click($contextSelector);
        $I->click($option, $contextSelector);
        $this->waitForReloadForm();
    }

    public function selectLinearOption($option, $contextSelector)
    {
        $I = $this->tester;
        $I->waitForElementVisible($contextSelector);
        $I->click($option, $contextSelector);
        $this->waitForReloadForm();
    }
}
