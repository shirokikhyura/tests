<?php

namespace Page\PayOp;

class LoginPage extends \Page\BasePage
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
}