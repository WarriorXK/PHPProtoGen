<?php
/**
 * Created by PhpStorm.
 * User: kevinmeijer
 * Date: 15/02/2018
 * Time: 15:18
 */
declare(strict_types = 1);

namespace WarriorXK\PHPProtoGen;

class Message {

    /**
     * @var \WarriorXK\PHPProtoGen\Field[][]
     */
    protected $_oneOfGroups = [];

    /**
     * @var bool[]
     */
    protected $_reservedTags = [];

    /**
     * @var \WarriorXK\PHPProtoGen\Field[]
     */
    protected $_fields = [];

    /**
     * @var \WarriorXK\PHPProtoGen\File|null
     */
    protected $_file = NULL;

    /**
     * @var string
     */
    protected $_name = NULL;

    public function __construct(string $name, array $fields = []) {

        $this->_name = $name;

        foreach ($fields as $field) {
            $this->addField($field);
        }

    }

    /**
     * @param \WarriorXK\PHPProtoGen\File $file
     */
    public function setFile(File $file) {
        $this->_file = $file;
    }

    /**
     * @return \WarriorXK\PHPProtoGen\File|null
     */
    public function getFile() {
        return $this->_file;
    }

    /**
     * Groups the provided fields in a new oneOf group, returns the group index
     *
     * @param string                         $groupName
     * @param \WarriorXK\PHPProtoGen\Field[] $fields
     */
    public function groupFieldsInOneOf(string $groupName, array $fields) {

        if (empty($fields)) {
            throw new \InvalidArgumentException('The provided array can not be empty!');
        }
        if (isset($this->_oneOfGroups[$groupName])) {
            throw new \LogicException('The groupName "' . $groupName . '" is already in use');
        }

        foreach ($fields as $key => $field) {

            if (!$field instanceof Field) {
                throw new \InvalidArgumentException('Object at index ' . $key . ' is not an instance of ' . Field::class);
            }

            if ($field->getMessage() !== $this) {
                throw new \LogicException('One of the provided fields "' . $field->getName() . '" is not a part of this message');
            }

            if ($this->getOneOfGroupNameForField($field) !== NULL) {
                throw new \LogicException('One of the provided fields "' . $field->getName() . '" is already part of a group');
            }

        }

        $this->_oneOfGroups[$groupName] = $fields;

    }

    /**
     * Adds the provided field to an existing oneOf group
     *
     * @param string                       $groupName
     * @param \WarriorXK\PHPProtoGen\Field $field
     * @param bool                         $allowCreation
     */
    public function addFieldToOneOfGroup(string $groupName, Field $field, bool $allowCreation = FALSE) {

        if ($field->getMessage() !== $this) {
            throw new \LogicException('The provided field "' . $field->getName() . '" is not apart of this message');
        }
        if (!isset($this->_oneOfGroups[$groupName])) {

            if ($allowCreation) {
                $this->_oneOfGroups[$groupName] = [];
            } else {
                throw new \OutOfRangeException('The provided group "' . $groupName . '" does not exist on this message');
            }

        }

        $currentGroupIndex = $this->getOneOfGroupNameForField($field);
        if ($currentGroupIndex === $groupName) {
            return; // Field is already a member of said group
        } elseif ($currentGroupIndex !== NULL) {
            throw new \LogicException('The provided field "' . $field->getName() . '" is already apart of another oneOf group ' . $currentGroupIndex);
        }

        $this->_oneOfGroups[$groupName][] = $field;

    }

    /**
     * Returns the groupName for the specified field
     *
     * @param \WarriorXK\PHPProtoGen\Field $testField
     *
     * @return string|null
     */
    public function getOneOfGroupNameForField(Field $testField) {

        foreach ($this->_oneOfGroups as $groupName => $oneOfGroup) {

            foreach ($oneOfGroup as $field) {

                if ($field === $testField) {
                    return $groupName;
                }

            }

        }

        return NULL;
    }

    /**
     * @param \WarriorXK\PHPProtoGen\Field $field
     */
    public function addField(Field $field) {

        $fieldName = $field->getName();
        if (isset($this->_fields[$fieldName])) {
            throw new \LogicException('Message "' . $this->getName() . '" already has a field named "' . $fieldName . '"');
        }

        $fieldTag = $field->getTag();
        if ($this->getFieldByTag($fieldTag) !== NULL) {
            throw new \LogicException('Message "' . $this->getName() . '" already has a field with tag "' . $fieldTag . '"');
        }

        $this->_fields[$fieldName] = $field;
        $field->setMessage($this);

    }

    /**
     * @param int $tag
     *
     * @return \WarriorXK\PHPProtoGen\Field|null
     */
    public function getFieldByTag(int $tag) {

        foreach ($this->getFields() as $field) {
            if ($field->getTag() === $tag) {
                return $field;
            }
        }

        return NULL;
    }

    /**
     * @return \WarriorXK\PHPProtoGen\Field[]
     */
    public function getFields() : array {
        return $this->_fields;
    }

    /**
     * @param int $start
     * @param int $end
     */
    public function removeReservedTagRange(int $start, int $end) {

        $range = range($start, $end);
        foreach ($range as $tag) {
            unset($this->_reservedTags[$tag]);
        }

    }

    /**
     * @param int $start
     * @param int $end
     */
    public function addReservedTagRange(int $start, int $end) {

        $range = range($start, $end);
        foreach ($range as $tag) {
            $this->_reservedTags[$tag] = TRUE;
        }

    }

    /**
     * @param int $tag
     */
    public function removeReservedTag(int $tag) {
        unset($this->_reservedTags[$tag]);
    }

    /**
     * @param int $tag
     */
    public function addReservedTag(int $tag) {
        $this->_reservedTags[$tag] = TRUE;
    }

    /**
     * @return string
     */
    public function getName() : string {
        return $this->_name;
    }

    protected function _reservedTagsStr() : string {

        if (empty($this->_reservedTags)) {
            return '';
        }

        // Todo: Attempt to minimize the listing by using the to keyword ('reserved 1, 2, 9 to 11;')

        return 'reserved ' . implode(', ', array_keys($this->_reservedTags)) . ';';
    }

    public function getFQMN() : string {

        $file = $this->getFile();
        if ($file === NULL) {
            throw new \LogicException('A message needs to be added to a file before its FQMN can be determined');
        }

        $parts = [];

        $filePackage = $file->getPackage();
        if ($filePackage !== NULL) {
            $parts[] = $filePackage;
        }

        $parts[] = $this->getName();

        return implode('.', $parts);
    }

    /**
     * @return string
     */
    public function exportToString() : string {

        $in = '    ';

        $str = 'message ' . $this->getName() . ' {' . PHP_EOL;

        $reservedStr = $this->_reservedTagsStr();

        if ($reservedStr !== '') {
            $str .= $in . $reservedStr . PHP_EOL;
        }

        foreach ($this->getFields() as $field) {

            if ($this->getOneOfGroupNameForField($field) !== NULL) {
                continue;
            }

            $str .= $in . $field->exportToString() . PHP_EOL;

        }

        foreach ($this->_oneOfGroups as $groupName => $fields) {

            $str .= $in . 'oneof ' . $groupName . ' {' . PHP_EOL;

            foreach ($fields as $field) {
                $str .= $in . $in . $field->exportToString() . PHP_EOL;
            }

            $str .= $in . '}' . PHP_EOL;

        }

        return $str . '}';
    }
}
