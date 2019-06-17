<?php
/**
 * Created by PhpStorm.
 * User: Marios
 * Date: 01/02/2019
 * Time: 13:19
 */

class userBehaviour extends MonoBehaviour
{
    protected $userAuth;
    protected $userData = [];
    protected $users;

    public function __construct()
    {
        parent::__construct();
        $this->userAuth = new userAuthentications();
        $this->users = new users();
        $this->userAuth->stopUnauthUser();
        $this->userData = $this->userAuth->getUserData();
    }

    public function restrictActionTo($userTypes = [])
    {
        if(count($userTypes) > 0 && !in_array($this->userData["userType"], $userTypes))
        {
            generic::errorToDisplayEnc("You are not authorised to access this section of the page, as you are not authorised!");
        }
    }

    public function __destruct()
    {
        unset($this->userAuth);
        unset($this->userData);
        unset($this->users);
        parent::__destruct();
    }
}