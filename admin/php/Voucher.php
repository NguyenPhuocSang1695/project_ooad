<?php
require_once './connect.php';

/**
 * Class Voucher - Chứa thông tin cơ bản của voucher
 */
class Voucher
{
    protected $id;
    protected $name;
    protected $percen_decrease;
    protected $conditions;
    protected $status;

    public function __construct($id = null, $name = '', $percen_decrease = 0, $conditions = 0, $status = '')
    {
        $this->id = $id;
        $this->name = $name;
        $this->percen_decrease = $percen_decrease;
        $this->conditions = $conditions;
        $this->status = $status;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPercenDecrease()
    {
        return $this->percen_decrease;
    }

    public function getConditions()
    {
        return $this->conditions;
    }

    public function getStatus()
    {
        return $this->status;
    }

    // Setters
    public function setId($id)
    {
        $this->id = $id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setPercenDecrease($percen_decrease)
    {
        $this->percen_decrease = $percen_decrease;
    }

    public function setConditions($conditions)
    {
        $this->conditions = $conditions;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }
}
