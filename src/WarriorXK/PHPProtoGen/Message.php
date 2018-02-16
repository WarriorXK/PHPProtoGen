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
     * @return null|\WarriorXK\PHPProtoGen\File
     */
    public function getFile() {
        return $this->_file;
    }

    /**
     * @param \WarriorXK\PHPProtoGen\Field $field
     */
    public function addField(Field $field) {

        $fieldName = $field->getName();
        if (isset($this->_fields[$fieldName])) {
            throw new \LogicException('Message "' . $this->getName() . '" already has a field named "' . $fieldName . '"');
        }

        $this->_fields[$fieldName] = $field;
        $field->setMessage($this);

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

    /**
     * @return string
     */
    public function exportToString() : string {

        $in = '    ';

        $str  = 'message ' . $this->getName() . ' {' . PHP_EOL;

        $reservedStr = $this->_reservedTagsStr();

        if ($reservedStr !== '') {
            $str .= $in . $reservedStr . PHP_EOL;
        }

        foreach ($this->getFields() as $field) {
            $str .= $in . $field->exportToString() . PHP_EOL;
        }

        return $str . '}';
    }

}
