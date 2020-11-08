<?php

namespace Page\PayOp\Ui;

class LoginPage extends \Page\BaseUiPage
{
    public static $URL = '/en/auth/login';

    public static $loginBlockEmailInput = '[name="email"]';
    public static $loginBlockPasswordInput = '[name="password"]';
    public static $loginBlockSubmitButton = '.login__submit';

    public function amOnThisPage()
    {
        $this->tester->waitForElementVisible(self::$loginBlockSubmitButton, 10);
    }

    public function fillForm($email, $password)
    {
        $this->tester->fillField(static::$loginBlockEmailInput, $email);
        $this->tester->fillField(static::$loginBlockPasswordInput, $password);
    }

    public function submitLoginForm()
    {
        $this->clickElement(static::$loginBlockSubmitButton);
    }

    public function compareThisPageForDevice($device)
    {
        $this->comparePageForDevice($device, []);
    }
}