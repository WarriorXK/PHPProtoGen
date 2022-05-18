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

    const MAP_KEY_TYPES = [
        Utility::TYPE_INT32,
        Utility::TYPE_SINT32,
        Utility::TYPE_UINT32,
        Utility::TYPE_INT64,
        Utility::TYPE_SINT64,
        Utility::TYPE_UINT64,
        Utility::TYPE_STRING,
        Utility::TYPE_BOOL,
    ];

    /**
     * @var string|null
     */
    protected $_sourceFilePath = NULL;

    /**
     * @var bool
     */
    protected $_repeatable = FALSE;

    /**
     * @var \WarriorXK\PHPProtoGen\FieldType|null
     */
    protected $_valueType = NULL;

    /**
     * @var bool
     */
    protected $_optional = FALSE;

    /**
     * @var \WarriorXK\PHPProtoGen\FieldType|null
     */
    protected $_keyType = NULL;

    /**
     * @var \WarriorXK\PHPProtoGen\Field|null
     */
    protected $_field = NULL;

    /**
     * @var string
     */
    protected $_type = NULL;

    public static function Any(bool $repeatable = FALSE, bool $optional = FALSE) {

        $type = new static(Utility::MESSAGE_ANY, $repeatable, $optional);
        $type->_sourceFilePath = 'google/protobuf/any.proto';

        return $type;
    }

    public static function Timestamp(bool $repeatable = FALSE, bool $optional = FALSE) {

        $type = new static(Utility::MESSAGE_TIMESTAMP, $repeatable, $optional);
        $type->_sourceFilePath = 'google/protobuf/timestamp.proto';

        return $type;
    }

    public static function Int(bool $repeatable = FALSE, bool $optional = FALSE) {
        return new static(Utility::TYPE_INT64, $repeatable, $optional);
    }

    public static function SInt(bool $repeatable = FALSE, bool $optional = FALSE) {
        return new static(Utility::TYPE_SINT64, $repeatable, $optional);
    }

    public static function UInt(bool $repeatable = FALSE, bool $optional = FALSE) {
        return new static(Utility::TYPE_UINT64, $repeatable, $optional);
    }

    public static function Float(bool $repeatable = FALSE, bool $optional = FALSE) {
        return new static(Utility::TYPE_FLOAT, $repeatable, $optional);
    }

    public static function Double(bool $repeatable = FALSE, bool $optional = FALSE) {
        return new static(Utility::TYPE_DOUBLE, $repeatable, $optional);
    }

    public static function String(bool $repeatable = FALSE, bool $optional = FALSE) {
        return new static(Utility::TYPE_STRING, $repeatable, $optional);
    }

    public static function Bool(bool $repeatable = FALSE, bool $optional = FALSE) {
        return new static(Utility::TYPE_BOOL, $repeatable, $optional);
    }

    public static function Bytes(bool $repeatable = FALSE, bool $optional = FALSE) {
        return new static(Utility::TYPE_BYTES, $repeatable, $optional);
    }

    public static function Enum(Enum $enum, $repeatable = FALSE, bool $optional = FALSE) {
        return new static($enum->getFQMN(), $repeatable, $optional);
    }

    public static function Map(self $keyType, self $valueType) {

        $map = new static(Utility::TYPE_MAP, FALSE, FALSE);
        $map->setValueType($valueType);
        $map->setKeyType($keyType);

        return $map;
    }

    public static function Struct(bool $repeatable = FALSE, bool $optional = FALSE) {
        return new static(Utility::MESSAGE_STRUCT, $repeatable, $optional);
    }

    public static function Value(bool $repeatable = FALSE, bool $optional = FALSE) {
        return new static(Utility::MESSAGE_VALUE, $repeatable, $optional);
    }

    public static function Message(Message $message, bool $repeatable = FALSE, bool $optional = FALSE) {

        $type = new static($message->getFQMN(), $repeatable, $optional);
        $type->_sourceFilePath = $message->getFile()->getPath();

        return $type;
    }

    public function __construct(string $type, bool $repeatable = FALSE, bool $optional = FALSE) {

        $this->setRepeatable($repeatable);
        $this->setOptional($optional);
        $this->setType($type);

    }

    public function getSourceFilePath() {
        return $this->_sourceFilePath;
    }

    public function setValueType(self $type) {

        if ($this->getType() !== Utility::TYPE_MAP) {
            throw new \LogicException('The property ValueType is only available for map types');
        }

        $this->_valueType = $type;

    }

    public function getValueType() {
        return $this->_valueType;
    }

    public function setOptional(bool $optional) {

        if ($this->isRepeatable() && $optional) {
            throw new \RuntimeException('FieldType cannot be optional and repeatable at the same time');
        }

        $this->_optional = $optional;
    }

    public function isOptional() : bool {
        return $this->_optional;
    }

    public function setKeyType(self $type) {

        if ($this->getType() !== Utility::TYPE_MAP) {
            throw new \LogicException('The property KeyType is only available for map types');
        }

        $strType = $type->getType();
        if (!in_array($strType, self::MAP_KEY_TYPES, TRUE)) {
            throw new \LogicException('Invalid map key type ' . $strType);
        }

        $this->_keyType = $type;

    }

    public function getKeyType() {
        return $this->_keyType;
    }

    public function setField(Field $field = NULL) {
        $this->_field = $field;
    }

    public function getField() {
        return $this->_field;
    }

    public function setRepeatable(bool $repeatable) {

        if ($repeatable && $this->isOptional()) {
            throw new \RuntimeException('FieldType cannot be optional and repeatable at the same time');
        }

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

        $strType = $this->getType();
        if ($strType === Utility::TYPE_MAP) {

            $valueType = $this->getValueType();
            if ($valueType === NULL) {
                throw new \RuntimeException('No value type set for map');
            }

            $keyType = $this->getKeyType();
            if ($keyType === NULL) {
                throw new \RuntimeException('No key type set for map');
            }

            return $strType . '<' . $keyType->getType() . ', ' . $valueType->getType() . '>';
        }

        $prefix = '';
        if ($this->isOptional()) {
            $prefix = 'optional ';
        } elseif ($this->isRepeatable()) {
            $prefix = 'repeated ';
        }

        return $prefix . $strType;
    }
}
