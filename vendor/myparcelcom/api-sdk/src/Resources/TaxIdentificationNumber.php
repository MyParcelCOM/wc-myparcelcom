<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Enums\TaxTypeEnum;
use MyParcelCom\ApiSdk\Exceptions\MyParcelComException;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;

class TaxIdentificationNumber
{
    use JsonSerializable;

    /** @var string */
    private $countryCode;

    /** @var string */
    private $number;

    /** @var string|null */
    private $description;

    /** @var string */
    private $type;

    /**
     * @param string $countryCode
     * @return $this
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $number
     * @return $this
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string|null $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param TaxTypeEnum $type
     * @return $this
     */
    public function setType($type)
    {
        if (!($type instanceof TaxTypeEnum)) {
            throw new MyParcelComException('Expected parameter of type \MyParcelCom\ApiSdk\Enums\TaxTypeEnum');
        }

        $this->type = $type->getValue();

        return $this;
    }

    /**
     * @return TaxTypeEnum|null
     */
    public function getType()
    {
        return $this->type ? new TaxTypeEnum($this->type) : null;
    }
}
