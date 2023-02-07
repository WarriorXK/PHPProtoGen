<?php

declare(strict_types = 1);

/**
 * Created by PhpStorm.
 * User: kevinmeijer
 * Date: 07/06/2018
 * Time: 10:12
 */

namespace WarriorXK\PHPProtoGen;

class Enum {

    /**
     * @var bool
     */
    protected $_allowAlias = FALSE;

    /**
     * @var int[]
     */
    protected $_options = [];

    /**
     * @var string
     */
    protected $_name = NULL;

    /**
     * @var \WarriorXK\PHPProtoGen\File|null
     */
    protected $_file = NULL;

    /**
     * Enum constructor.
     *
     * @param string $name
     */
    public function __construct(string $name) {
        $this->_name = $name;
    }

    /**
     * @return string
     */
    public function getName() : string {
        return $this->_name;
    }

    /**
     * Returns FALSE if the value cannot be changed
     *
     * @param bool $allow
     *
     * @return bool
     */
    public function setAllowAlias(bool $allow) : bool {

        if (!$allow) {

            $values = array_count_values($this->_options);
            foreach ($values as $count) {
                if ($count > 1) {
                    return FALSE;
                }
            }

        }

        $this->_allowAlias = $allow;

        return TRUE;
    }

    /**
     * @return bool
     */
    public function getAllowAlias() : bool {
        return $this->_allowAlias;
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
     * @param string $name
     * @param int    $val
     */
    public function setOption(string $name, int $val) {

        $key = array_search($val, $this->_options, TRUE);
        if (!$this->getAllowAlias() && $key !== FALSE) {
            throw new \InvalidArgumentException('Value ' . $val . ' already exists as key ' . $key . ' and aliasses are not allowed');
        }

        $this->_options[$name] = $val;

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

        $str = 'enum ' . $this->getName() . ' {' . PHP_EOL;

        if ($this->getAllowAlias()) {
            $str .= $in . 'option allow_alias = true;' . PHP_EOL;
        }

        foreach ($this->_options as $key => $value) {
            $str .= $in . $key . ' = ' . $value . ';' . PHP_EOL;
        }

        return $str . '}';
    }
}
