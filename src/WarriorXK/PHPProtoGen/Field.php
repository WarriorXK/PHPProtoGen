<?php
/**
 * Created by PhpStorm.
 * User: kevinmeijer
 * Date: 15/02/2018
 * Time: 15:21
 */
declare(strict_types = 1);

namespace WarriorXK\PHPProtoGen;

class Field {

    const OPTION_DEPRECATED = 0b00000001;

    /**
     * @var \WarriorXK\PHPProtoGen\Message|null
     */
    protected $_message = NULL;

    /**
     * @var int
     */
    protected $_options = 0;

    /**
     * @var \WarriorXK\PHPProtoGen\FieldType
     */
    protected $_type = NULL;

    /**
     * @var string
     */
    protected $_name = NULL;

    /**
     * @var int|null
     */
    protected $_tag = NULL;

    public function __construct(string $name, FieldType $type, int $tag = NULL, int $options = 0) {

        $this->_name = $name;

        $this->setTag($tag);
        $this->setType($type);
        $this->setOptions($options);

    }

    /**
     * @param \WarriorXK\PHPProtoGen\Message $message
     */
    public function setMessage(Message $message) {
        $this->_message = $message;
    }

    /**
     * @return \WarriorXK\PHPProtoGen\Message|null
     */
    public function getMessage() {
        return $this->_message;
    }

    public function setType(FieldType $type) {

        if ($this->_type) {
            $this->_type->setField(NULL);
        }

        $type->setField($this);
        $this->_type = $type;

    }

    public function getType() : FieldType {
        return $this->_type;
    }

    /**
     * @param int $options
     */
    public function setOptions(int $options) {
        $this->_options = $options;
    }

    /**
     * @param int $options
     */
    public function addOptions(int $options) {
        $this->_options |= $options;
    }

    /**
     * @param int $options
     */
    public function removeOptions(int $options) {
        $this->_options &= ~$options;
    }

    /**
     * @param int $prop
     *
     * @return bool
     */
    public function hasOption(int $prop) : bool {
        return (bool) ($this->_options & $prop);
    }

    /**
     * @return string
     */
    public function getName() : string {
        return $this->_name;
    }

    /**
     * @param int|null
     */
    public function setTag(int $tag = NULL) {
        $this->_tag = $tag;
    }

    /**
     * @return int|null
     */
    public function getTag() {
        return $this->_tag;
    }

    public function getGenerator() {

        $message = $this->getMessage();
        if ($message !== NULL) {

            $file = $message->getFile();
            if ($file !== NULL) {
                return $file->getGenerator();
            }

        }

        return NULL;
    }

    /**
     * @return string
     */
    public function exportToString() : string {

        $generator = $this->getGenerator();
        if ($generator === NULL) {
            throw new \LogicException('Unable to export field to string without a generator!');
        }

        $tag = $this->getTag() ?: $generator->getTagForField($this);
        if ($tag === NULL) {
            throw new \LogicException('Field ' . $this->getMessage()->getName() . '->' . $this->getName() . ' has no tag!');
        }

        $str = $this->_type->exportToString() . ' ' . $this->getName() . ' = ' . $tag;

        $options = [];
        if ($this->hasOption(static::OPTION_DEPRECATED)) {
            $options[] = 'deprecated=true';
        }

        // Todo: Validate that multiple options are seperated by commas (Currently no implementation exists)

        return $str . (empty($options) ? '' : ' [' . implode(', ', $options) . ']') . ';';
    }
}
