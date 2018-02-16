<?php
/**
 * Created by PhpStorm.
 * User: kevinmeijer
 * Date: 15/02/2018
 * Time: 16:33
 */
declare(strict_types = 1);

namespace WarriorXK\PHPProtoGen;

class FieldType {

    /**
     * @var bool
     */
    protected $_repeatable = FALSE;

    /**
     * @var \WarriorXK\PHPProtoGen\Field|null
     */
    protected $_field = NULL;

    /**
     * @var string
     */
    protected $_type = NULL;

    public static function Any(bool $repeatable = FALSE) {
        return new static(Utility::TYPE_ANY, $repeatable);
    }

    public static function Int(bool $repeatable = FALSE) {
        return new static('int64', $repeatable);
    }

    public static function Float(bool $repeatable = FALSE) {
        return new static('float', $repeatable);
    }

    public static function String(bool $repeatable = FALSE) {
        return new static('string', $repeatable);
    }

    public static function Bool(bool $repeatable = FALSE) {
        return new static('bool', $repeatable);
    }

    public static function Bytes(bool $repeatable = FALSE) {
        return new static('bytes', $repeatable);
    }

    public function __construct(string $type, bool $repeatable = FALSE) {

        $this->setRepeatable($repeatable);
        $this->setType($type);

    }

    public function setField(Field $field = NULL) {
        $this->_field = $field;
    }

    public function getField() {
        return $this->_field;
    }

    public function setRepeatable(bool $repeatable) {
        $this->_repeatable = $repeatable;
    }

    public function isRepeatable() : bool {
        return $this->_repeatable;
    }

    public function setType(string $type) {
        $this->_type = $type; // Todo: Validate types?
    }

    public function getType() : string {
        return $this->_type;
    }

    public function exportToString() : string {
        return ($this->isRepeatable() ? 'repeated ' : '') . $this->getType();
    }
}
