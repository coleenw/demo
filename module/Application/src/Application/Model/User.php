<?php

namespace Application\Model;

class User
{
    private $fullName;
    private $age;

    public function getFullName()
    {
        return $this->fullName;
    }

    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
    }

    public function getAge()
    {
        return $this->age;
    }

    public function setAge($age)
    {
        $this->age = $age;
    }

    public function getArray()
    {
        return [
            'fullname' => $this->getFullName(),
            'age'       => $this->getAge()
        ];
    }
}