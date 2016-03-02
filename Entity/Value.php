<?php

namespace Sidus\EAVModelBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Utilities\DateTimeUtility;

abstract class Value implements ContextualValueInterface
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Data
     * @ORM\ManyToOne(targetEntity="Sidus\EAVModelBundle\Entity\Data", inversedBy="values", fetch="EAGER")
     * @ORM\JoinColumn(name="data_id", referencedColumnName="id", onDelete="cascade", nullable=false)
     */
    protected $data;

    /**
     * @var Data
     * @ORM\ManyToOne(targetEntity="Sidus\EAVModelBundle\Entity\Data", cascade={"persist"})
     * @ORM\JoinColumn(name="data_value_id", referencedColumnName="id", onDelete="cascade", nullable=true)
     */
    protected $dataValue;

    /**
     * @var string
     * @ORM\Column(name="attribute_code", type="string", length=255)
     */
    protected $attributeCode;

    /**
     * @var integer
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    protected $position;

    /**
     * @var boolean
     * @ORM\Column(name="bool_value", type="boolean", nullable=true)
     */
    protected $boolValue;

    /**
     * @var integer
     * @ORM\Column(name="integer_value", type="integer", nullable=true)
     */
    protected $integerValue;

    /**
     * @var float
     * @ORM\Column(name="decimal_value", type="float", nullable=true)
     */
    protected $decimalValue;

    /**
     * @var DateTime
     * @ORM\Column(name="date_value", type="date", nullable=true)
     */
    protected $dateValue;

    /**
     * @var DateTime
     * @ORM\Column(name="datetime_value", type="datetime", nullable=true)
     */
    protected $datetimeValue;

    /**
     * @var string
     * @ORM\Column(name="string_value", type="string", length=255, nullable=true)
     */
    protected $stringValue;

    /**
     * @var string
     * @ORM\Column(name="text_value", type="text", nullable=true)
     */
    protected $textValue;

    /**
     * @param Data $data
     * @param AttributeInterface $attribute
     */
    public function __construct(Data $data = null, AttributeInterface $attribute = null)
    {
        $this->data = $data;
        if ($attribute) {
            $this->attributeCode = $attribute->getCode();
        }
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set attributeCode
     *
     * @param string $attributeCode
     * @return Value
     */
    public function setAttributeCode($attributeCode)
    {
        $this->attributeCode = $attributeCode;
        return $this;
    }

    /**
     * Get attributeCode
     *
     * @return string
     */
    public function getAttributeCode()
    {
        return $this->attributeCode;
    }

    /**
     * Set boolValue
     *
     * @param boolean $boolValue
     * @return Value
     */
    public function setBoolValue($boolValue)
    {
        $this->boolValue = $boolValue;
        return $this;
    }

    /**
     * Get boolValue
     *
     * @return boolean
     */
    public function getBoolValue()
    {
        return $this->boolValue;
    }

    /**
     * Set integerValue
     *
     * @param integer $integerValue
     * @return Value
     */
    public function setIntegerValue($integerValue)
    {
        $this->integerValue = $integerValue;
        return $this;
    }

    /**
     * Get integerValue
     *
     * @return integer
     */
    public function getIntegerValue()
    {
        return $this->integerValue;
    }

    /**
     * Set decimalValue
     *
     * @param float $decimalValue
     * @return Value
     */
    public function setDecimalValue($decimalValue)
    {
        $this->decimalValue = $decimalValue;
        return $this;
    }

    /**
     * Get decimalValue
     *
     * @return float
     */
    public function getDecimalValue()
    {
        return $this->decimalValue;
    }

    /**
     * Set dateValue
     *
     * @param DateTime|int|string $dateValue
     * @return Value
     * @throws \UnexpectedValueException
     */
    public function setDateValue($dateValue)
    {
        $this->dateValue = DateTimeUtility::parse($dateValue);
        return $this;
    }

    /**
     * Get dateValue
     *
     * @return DateTime
     */
    public function getDateValue()
    {
        return $this->dateValue;
    }

    /**
     * Set datetimeValue
     *
     * @param DateTime|int|string $datetimeValue
     * @return Value
     * @throws \UnexpectedValueException
     */
    public function setDatetimeValue($datetimeValue)
    {
        $this->datetimeValue = DateTimeUtility::parse($datetimeValue);
        return $this;
    }

    /**
     * Get datetimeValue
     *
     * @return DateTime
     */
    public function getDatetimeValue()
    {
        return $this->datetimeValue;
    }

    /**
     * Set stringValue
     *
     * @param string $stringValue
     * @return Value
     */
    public function setStringValue($stringValue)
    {
        $this->stringValue = $stringValue;

        return $this;
    }

    /**
     * Get stringValue
     *
     * @return string
     */
    public function getStringValue()
    {
        return $this->stringValue;
    }

    /**
     * Set textValue
     *
     * @param string $textValue
     * @return Value
     */
    public function setTextValue($textValue)
    {
        $this->textValue = $textValue;

        return $this;
    }

    /**
     * Get textValue
     *
     * @return string
     */
    public function getTextValue()
    {
        return $this->textValue;
    }

    /**
     * Set dataValue
     *
     * @param integer $dataValue
     * @return Value
     */
    public function setDataValue($dataValue)
    {
        $this->dataValue = $dataValue;

        return $this;
    }

    /**
     * Get dataValue
     *
     * @return integer
     */
    public function getDataValue()
    {
        return $this->dataValue;
    }

    /**
     * @return Data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param Data $data
     */
    public function setData(Data $data = null)
    {
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        $context = [];
        foreach ($this->getContextKeys() as $key) {
            $context[$key] = $this->$key;
        }
        return $context;
    }

    /**
     * @param string $key
     * @throws \UnexpectedValueException
     */
    protected function checkContextKey($key)
    {
        if (!in_array($key, $this->getContextKeys(), true)) {
            throw new \UnexpectedValueException("Trying to get an non-allowed context key {$key}");
        }
    }

    /**
     * @param string $key
     * @return mixed
     * @throws \UnexpectedValueException
     */
    public function getContextValue($key)
    {
        $this->checkContextKey($key);
        return $this->$key;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @throws \UnexpectedValueException
     */
    public function setContextValue($key, $value)
    {
        $this->checkContextKey($key);
        $this->$key = $value;
    }

    /**
     * Context constructor.
     * @param array $context
     * @throws \UnexpectedValueException
     */
    public function setContext(array $context)
    {
        $this->clearContext();
        foreach ($context as $key => $value) {
            $this->setContextValue($key, $value);
        }
    }

    /**
     * Clean all contextual keys
     */
    public function clearContext()
    {
        foreach ($this->getContextKeys() as $key) {
            $this->$key = null;
        }
    }

    /**
     * @return array
     */
    public function getContextKeys()
    {
        return [];
    }

    /**
     * Remove id on clone
     */
    public function __clone() {
        $this->id = null;
    }
}
