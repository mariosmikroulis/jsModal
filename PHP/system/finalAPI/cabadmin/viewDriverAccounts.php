<?php

class viewDriverAccounts extends userBehaviour
{
    private $driverList = [];

    public function __construct()
    {
        parent::__construct();

        $this->main();
    }

    private function main()
    {
        $this->restrictActionTo(["staff"]);
        $this->getList();
        $this->removeImportantParameters();
        generic::successEncDisplay($this->driverList);
    }

    private function getList()
    {
        $this->driverList = $this->dbCon->fetch("driverAccounts", [], true, "ORDER BY `allowAccess` DESC, `driverID` ASC");
    }

    private function removeImportantParameters()
    {
        for($i=0; $i < count($this->driverList); $i++)
        {
            unset($this->driverList[$i]["driverPassword"]);
            unset($this->driverList[$i]["hashSalt"]);
        }
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}