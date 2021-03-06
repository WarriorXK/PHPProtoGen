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
     * @var int[]
     */
    protected $_reservedTags = [];

    /**
     * @var \WarriorXK\PHPProtoGen\Message[]
     */
    protected $_messages = [];

    /**
     * @var string
     */
    protected $_comment = '';

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
     * @param string $comment
     *
     * @return void
     */
    public function setComment(string $comment) {
        $this->_comment = $comment;
    }

    /**
     * @return string
     */
    public function getComment() : string {
        return $this->_comment;
    }

    /**
     * @param \WarriorXK\PHPProtoGen\Message $message
     *
     * @return void
     */
    public function addMessage(self $message) {

        $theirFile = $message->getFile();
        $ourFile = $this->getFile();

        if ($ourFile === NULL) {
            throw new \LogicException('A message needs to be added to a file before calling addMessage on it');
        }

        if ($theirFile === NULL) {
            $message->setFile($ourFile);
        } elseif ($theirFile !== $ourFile) {
            throw new \LogicException('Message ' . $message->getFQMN() . ' has already been added to a different file');
        }

        $fqmn = $message->getFQMN();
        if (isset($this->_messages[$fqmn])) {
            throw new \LogicException('Message ' . $this->getFQMN() . ' already has a child message qih FQMN ' . $fqmn);
        }

        $this->_messages[$fqmn] = $message;

    }

    /**
     * @return \WarriorXK\PHPProtoGen\Message[]
     */
    public function getMessages() : array {
        return $this->_messages;
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
            $this->_reservedTags[$tag] = $tag;
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
        $this->_reservedTags[$tag] = $tag;
    }

    /**
     * @return string
     */
    public function getName() : string {
        return $this->_name;
    }

    /**
     * @return string
     */
    protected function _reservedTagsStr() : string {

        if (empty($this->_reservedTags)) {
            return '';
        }

        $strRanges = [];

        $ranges = Utility::RangesFromInts($this->_reservedTags);
        foreach ($ranges as $range) {

            if ($range['min'] === $range['max']) {
                $strRanges[] = (string) $range['min'];
            } elseif (($range['min'] + 1) === $range['max']) { // Range of 2, convert it to 2 entries
                $strRanges[] = (string) $range['min'];
                $strRanges[] = (string) $range['max'];
            } else {
                $strRanges[] = $range['min'] . ' to ' . $range['max'];
            }

        }

        return 'reserved ' . implode(', ', $strRanges) . ';';
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
     * @param int $indentationLevel
     *
     * @return string
     */
    public function exportToString(int $indentationLevel = 0) : string {

        $singleIndent = '    ';
        $in = str_repeat($singleIndent, $indentationLevel);

        $commentsStr = Utility::GenerateComment($this->getComment(), $indentationLevel);
        if (strlen($commentsStr) > 0) {
            $commentsStr .= PHP_EOL;
        }

        $str = $commentsStr . $in . 'message ' . $this->getName() . ' {' . PHP_EOL;

        $reservedStr = $this->_reservedTagsStr();

        if ($reservedStr !== '') {
            $str .= $in . $singleIndent . $reservedStr . PHP_EOL;
        }

        foreach ($this->getMessages() as $message) {
            $str .= $message->exportToString($indentationLevel + 1) . PHP_EOL;
        }

        foreach ($this->getFields() as $field) {

            if ($this->getOneOfGroupNameForField($field) !== NULL) {
                continue;
            }

            $str .= $field->exportToString($indentationLevel + 1) . PHP_EOL;

        }

        foreach ($this->_oneOfGroups as $groupName => $fields) {

            $str .= $in . $singleIndent . 'oneof ' . $groupName . ' {' . PHP_EOL;

            foreach ($fields as $field) {
                $str .= $field->exportToString($indentationLevel + 2) . PHP_EOL;
            }

            $str .= $in . $singleIndent . '}' . PHP_EOL;

        }

        return $str . $in . '}';
    }
}
