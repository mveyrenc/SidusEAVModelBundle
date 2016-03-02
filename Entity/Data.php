<?php

namespace Sidus\EAVModelBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use JMS\Serializer\Annotation as JMS;
use LogicException;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVModelBundle\Utilities\DateTimeUtility;
use Symfony\Component\PropertyAccess\PropertyAccess;
use UnexpectedValueException;
use Sidus\EAVModelBundle\Validator\Constraints\Data as DataConstraint;

/**
 * @DataConstraint()
 */
abstract class Data implements DataInterface
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Data
     * @ORM\ManyToOne(targetEntity="Sidus\EAVModelBundle\Entity\Data", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $parent;

    /**
     * @var Data[]
     * @ORM\OneToMany(targetEntity="Sidus\EAVModelBundle\Entity\Data", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $children;

    /**
     * @var Value[]|Collection
     * @ORM\OneToMany(targetEntity="Sidus\EAVModelBundle\Entity\Value", mappedBy="data", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @JMS\Exclude()
     */
    protected $values;

    /**
     * @var DateTime
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var DateTime
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @var FamilyInterface
     * @ORM\Column(name="family_code", type="sidus_family", length=255)
     * @JMS\Exclude()
     */
    protected $family;

    /**
     * @var int
     * @ORM\Column(name="current_version", type="integer")
     */
    protected $currentVersion = 0;

    /**
     * @var array
     */
    protected $currentContext;

    /**
     * Initialize the data with an optional (but recommended family code)
     *
     * @param FamilyInterface $family
     * @throws LogicException
     */
    public function __construct(FamilyInterface $family)
    {
        if (!$family->isInstantiable()) {
            throw new LogicException("Family {$family->getCode()} is not instantiable");
        }
        $this->family = $family;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->values = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param DateTime $createdAt
     * @return Data
     * @throws UnexpectedValueException
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = DateTimeUtility::parse($createdAt, false);

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $updatedAt
     * @return Data
     * @throws UnexpectedValueException
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = DateTimeUtility::parse($updatedAt, false);

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param Data $parent
     * @return Data
     */
    public function setParent(Data $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Data
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("family")
     * @return string
     */
    public function getFamilyCode()
    {
        return $this->getFamily()->getCode();
    }

    /**
     * Return all values matching the attribute code
     *
     * @param AttributeInterface|null $attribute
     * @param array $context
     * @return Collection|Value[]
     * @throws UnexpectedValueException
     */
    public function getValues(AttributeInterface $attribute = null, array $context = null)
    {
        if (!$context) {
            $context = $this->getCurrentContext();
        }
        if (null === $attribute) {
            $values = new ArrayCollection();
            foreach ($this->values as $value) {
                $attribute = $this->getFamily()->getAttribute($value->getAttributeCode());
                if ($attribute->isContextMatching($value, $context)) {
                    $values->add($value);
                }
            }
            return $values;
        }
        $this->checkAttribute($attribute);
        $values = new ArrayCollection();
        foreach ($this->values as $value) {
            /** @noinspection NotOptimalIfConditionsInspection */
            if ($value->getAttributeCode() === $attribute->getCode() && $attribute->isContextMatching($value, $context)) {
                $values->add($value);
            }
        }
        return $values;
    }

    /**
     * Return first value found for attribute code in value collection
     *
     * @param AttributeInterface $attribute
     * @param array $context
     * @return null|Value
     * @throws UnexpectedValueException
     */
    public function getValue(AttributeInterface $attribute, array $context = null)
    {
        $values = $this->getValues($attribute, $context);
        return count($values) === 0 ? null : $values->first();
    }

    /**
     * Get the value data of the value matching the attribute
     *
     * @param AttributeInterface $attribute
     * @param array $context
     * @return mixed
     * @throws \Exception
     */
    public function getValueData(AttributeInterface $attribute, array $context = null)
    {
        $valuesData = $this->getValuesData($attribute, $context);
        return count($valuesData) === 0 ? null : $valuesData->first();
    }

    /**
     * Get the values data of multiple values for a given attribute
     *
     * @param AttributeInterface $attribute
     * @param array $context
     * @return mixed
     * @throws \Exception
     */
    public function getValuesData(AttributeInterface $attribute = null, array $context = null)
    {
        $valuesData = new ArrayCollection();
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($this->getValues($attribute, $context) as $value) {
            $valuesData->add($accessor->getValue($value, $attribute->getType()->getDatabaseType()));
        }
        return $valuesData;
    }

    /**
     * Set the value's data of a given attribute
     *
     * @param AttributeInterface $attribute
     * @param mixed $dataValue
     * @param array $context
     * @return Data
     * @throws Exception
     */
    public function setValueData(AttributeInterface $attribute, $dataValue, array $context = null)
    {
        return $this->setValuesData($attribute, [$dataValue], $context);
    }

    /**
     * Set the values' data of a given attribute for multiple fields
     *
     * @param AttributeInterface $attribute
     * @param array|\Traversable $dataValues
     * @param array $context
     * @return Data
     * @throws Exception
     */
    public function setValuesData(AttributeInterface $attribute, $dataValues, array $context = null)
    {
        if (!is_array($dataValues) && !$dataValues instanceof \Traversable) {
            throw new UnexpectedValueException('Datas must be an array or implements Traversable');
        }
        $this->emptyValues($attribute, $context);
        $accessor = PropertyAccess::createPropertyAccessor();
        $position = 0;
        foreach ($dataValues as $dataValue) {
            /** @noinspection DisconnectedForeachInstructionInspection */
            $value = $this->createValue($attribute, $context);
            $value->setPosition($position++);
            $accessor->setValue($value, $attribute->getType()->getDatabaseType(), $dataValue);
        }
        return $this;
    }

    /**
     * @param AttributeInterface $attribute
     * @param array $context
     * @return Data
     * @throws UnexpectedValueException
     */
    public function emptyValues(AttributeInterface $attribute = null, array $context = null)
    {
        $values = $this->getValues($attribute, $context);
        foreach ($values as $value) {
            $this->removeValue($value);
        }
        return $this;
    }

    /**
     * @param Value $value
     * @return Data
     * @throws UnexpectedValueException
     */
    public function addValue(Value $value)
    {
        if (!$value->getContext()) {
            $value->setContext($this->getCurrentContext());
        }
        $this->values->add($value);
        $value->setData($this);
        return $this;
    }

    /**
     * Append data to an attribute
     *
     * @param AttributeInterface $attribute
     * @param $valueData
     * @param array $context
     * @return Data
     * @throws \Exception
     */
    public function addValueData(AttributeInterface $attribute, $valueData, array $context = null)
    {
        $newValue = $this->createValue($attribute, $context);
        $accessor = PropertyAccess::createPropertyAccessor();
        $position = -1;
        foreach ($this->getValues($attribute, $context) as $value) {
            $position = max($position, $value->getPosition());
        }
        $newValue->setPosition($position + 1);
        $accessor->setValue($newValue, $attribute->getType()->getDatabaseType(), $valueData);
        return $this;
    }

    /**
     * @param Value $value
     * @return Data
     */
    public function removeValue(Value $value)
    {
        $this->values->removeElement($value);
        $value->setData(null);
        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getLabelValue()
    {
        return (string) $this->getValueData($this->getFamily()->getAttributeAsLabel());
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        try {
            return $this->getLabelValue();
        } catch (Exception $e) {
            return "[{$this->getId()}]";
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->getLabelValue();
        } catch (Exception $e) {
            return '';
        }
    }

    public function __call($methodName, $arguments)
    {
        if (0 === strpos($methodName, 'get')) {
            return $this->__get(lcfirst(substr($methodName, 3)));
        }
        $attribute = $this->getAttribute($methodName);
        if ($attribute) {
            return $this->__get(lcfirst($methodName));
        }
        $class = get_class($this);
        throw new \BadMethodCallException("Method '{$methodName}' for object '{$class}' with family '{$this->getFamilyCode()}' does not exist");
    }

    /**
     * Used to seemingly get values as if they were normal properties of this class
     *
     * @param string $name
     * @return mixed|null|Value
     * @throws \Exception
     */
    public function __get($name)
    {
        $attributeCode = $name;
        $returnData = true;
        if (substr($name, -5) === 'Value') {
            $returnData = false;
            $attributeCode = substr($name, 0, -5);
        }
        $attribute = $this->getAttribute($attributeCode);
        if (!$attribute) {
            throw new \BadMethodCallException("No attribute or method named {$name}");
        }

        if ($attribute->isMultiple()) {
            if ($returnData) {
                return $this->getValuesData($attribute);
            }
            return $this->getValues($attribute);
        }
        if ($returnData) {
            return $this->getValueData($attribute);
        }
        return $this->getValue($attribute);
    }

    /**
     * Used to seemingly set values as if they were normal properties of this class
     *
     * @param string $name
     * @param mixed|null|Value $value
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        $attributeCode = $name;
        $setData = true;
        if (substr($name, -5) === 'Value') {
            $setData = false;
            $attributeCode = substr($name, 0, -5);
        }
        $attribute = $this->getAttribute($attributeCode);
        if (!$attribute) {
            throw new \BadMethodCallException("No attribute or method named {$name}");
        }

        if ($attribute->isMultiple()) {
            if ($setData) {
                $this->setValuesData($attribute, $value);
                return;
            }
            foreach ($value as $v) {
                $this->addValue($v);
            }
            return;
        }
        if ($setData) {
            $this->setValueData($attribute, $value);
            return;
        }
        $this->addValue($value);
    }

    /**
     * @param $attributeCode
     * @return AttributeInterface
     */
    protected function getAttribute($attributeCode)
    {
        return $this->getFamily()->getAttribute($attributeCode);
    }

    /**
     * @return FamilyInterface
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * @param AttributeInterface $attribute
     * @param array $context
     * @return Value
     */
    public function createValue(AttributeInterface $attribute, array $context = null)
    {
        if (!$context) {
            $context = $this->getCurrentContext();
        }
        return $this->getFamily()->createValue($this, $attribute, $context);
    }

    /**
     * @param AttributeInterface $attribute
     * @param array $context
     * @return bool
     * @throws \Exception
     */
    public function isEmpty(AttributeInterface $attribute, array $context = null)
    {
        foreach ($this->getValuesData($attribute, $context) as $valueData) {
            if ($valueData !== null && $valueData !== '') {
                return false;
            }
        }
        return true;
    }

    /**
     * @param AttributeInterface $attribute
     * @throw UnexpectedValueException
     * @throws UnexpectedValueException
     */
    protected function checkAttribute(AttributeInterface $attribute)
    {
        if (!$this->getFamily()->hasAttribute($attribute->getCode())) {
            throw new UnexpectedValueException("Attribute {$attribute->getCode()} doesn't exists in family {$this->getFamilyCode()}");
        }
    }

    /**
     * @return int
     */
    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }

    /**
     * @param int $currentVersion
     */
    public function setCurrentVersion($currentVersion)
    {
        $this->currentVersion = $currentVersion;
    }

    /**
     * @return array
     */
    public function getCurrentContext()
    {
        if (!$this->currentContext) {
            return $this->getFamily()->getDefaultContext();
        }
        return $this->currentContext;
    }

    /**
     * @param array $currentContext
     */
    public function setCurrentContext(array $currentContext = [])
    {
        $this->currentContext = $currentContext;
    }

    /**
     * Remove id on clone and clean values
     * @throws UnexpectedValueException
     */
    public function __clone() {
        $this->id = null;
        $newValues = new ArrayCollection();
        foreach ($this->getValues() as $value) {
            $newValues[] = clone $value;
        }
        $this->emptyValues();
        foreach ($newValues as $newValue) {
            $this->addValue($newValue);
        }
        $this->setCreatedAt(new DateTime());
    }
}
