<?php

class passwordGenerator
{
    private $chars = array();
    private $passwordLength = 16;
    private $lengthLeft = 16;
    private $finalPassword = "";

    public function __construct()
    {
        $chars["capital"] = range("A", "Z");
        $chars["lower"] = range("a", "z");
        $chars["symbols"] = range("!", "+");
        $chars["numbers"] = range(0, 9);
    }

    private function load()
    {
        $this->lengthLeft = $this->passwordLength;
        $this->finalPassword = "";

        $chars["capital"] = range("A", "Z");
        $chars["lower"] = range("a", "z");
        $chars["symbols"] = range("!", "+");
        $chars["numbers"] = range(0, 9);
    }

    private function generate()
    {
        shuffle($this->chars);

        foreach ($this->chars as $key => $value)
        {
            for($i=0; $i < $this->setLength(); $i++)
            {
                $index = rand(0, count($this->chars[$key]));
                $this->finalPassword .= $this->chars[$key][$index];
                unset($this->chars[$key][$index]);
            }

            unset($this->chars[$key]);
        }

        $this->finalPassword = str_shuffle($this->finalPassword);
    }

    private function setLength()
    {
        $len = $this->lengthLeft;

        if(count($this->chars)>1) {
            $len = round($this->lengthLeft * rand(3, 5) * .1);

            if ($len % 2 > 0) {
                $len--;
            }

            $len = round(1, $len);
        }

        $this->lengthLeft -= $len;
        return $len;
    }

    public function createPassword()
    {
        $this->load();
        $this->generate();

        return $this->finalPassword;
    }
}