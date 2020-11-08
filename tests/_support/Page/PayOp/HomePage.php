<?php

namespace Page\PayOp;

class HomePage extends \Page\BasePage
{
    public static $loginButton = '.login-btn';

    public function clickOnLogin()
    {
        $this->clickElement(self::$loginButton);
//        $this->clickElement('.login_opener');
    }
}