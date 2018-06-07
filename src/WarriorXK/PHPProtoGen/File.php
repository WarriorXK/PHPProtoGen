<?php
/**
 * Created by PhpStorm.
 * User: kevinmeijer
 * Date: 15/02/2018
 * Time: 14:44
 */
declare(strict_types = 1);

namespace WarriorXK\PHPProtoGen;

class File {

    /**
     * @var int
     */
    protected $_syntaxVersion = NULL;

    /**
     * @var \WarriorXK\PHPProtoGen\Generator|null
     */
    protected $_generator = NULL;

    /**
     * @var \WarriorXK\PHPProtoGen\Message[]
     */
    protected $_messages = [];

    /**
     * @var string|null
     */
    protected $_package = NULL;

    /**
     * @var string[]
     */
    protected $_options = [];

    /**
     * @var \WarriorXK\PHPProtoGen\Import[]
     */
    protected $_imports = [];

    /**
     * @var \WarriorXK\PHPProtoGen\Enum[]
     */
    protected $_enums = [];

    /**
     * @var string
     */
    protected $_path = NULL;

    public function __construct(string $path, int $syntaxVersion = 3) {

        if ($path[0] === '/') {
            throw new \InvalidArgumentException('The provided path should be relative!');
        }

        $this->_path = $path;

        $this->setSyntaxVersion($syntaxVersion);

    }

    /**
     * @param \WarriorXK\PHPProtoGen\Generator $generator
     */
    public function setGenerator(Generator $generator) {
        $this->_generator = $generator;
    }

    /**
     * @return \WarriorXK\PHPProtoGen\Generator|null
     */
    public function getGenerator() {
        return $this->_generator;
    }

    /**
     * @param int $version
     */
    public function setSyntaxVersion(int $version) {

        if ($version !== 3) {
            throw new \BadMethodCallException('Only version 3 is supported currently');
        }

        $this->_syntaxVersion = $version;

    }

    /**
     * @return int
     */
    public function getSyntaxVersion() : int {
        return $this->_syntaxVersion;
    }

    /**
     * @param string|null $package
     */
    public function setPackage(string $package = NULL) {
        $this->_package = $package;
    }

    /**
     * @return string[]
     */
    public function getOptions() : array {
        return $this->_options;
    }

    /**
     * @param string      $option
     * @param string|null $value  Providing NULL will unset the value
     */
    public function setOption(string $option, string $value = NULL) {

        if ($value === NULL) {
            unset($this->_options[$option]);
        } else {
            $this->_options[$option] = $value;
        }

    }

    /**
     * @return string|null
     */
    public function getPackage() {
        return $this->_package;
    }

    /**
     * @param self $file
     *
     * @return bool
     */
    public function addFileImport(self $file) : bool {
        return $this->addImport(new Import($file->getPath()));
    }

    /**
     * @param \WarriorXK\PHPProtoGen\Import $import
     *
     * @return bool
     */
    public function addImport(Import $import) : bool {

        /** @var \WarriorXK\PHPProtoGen\Import|null $localImport */
        $importPath = $import->getPath();
        $localImport = $this->_imports[$importPath] ?? NULL;
        if ($localImport === NULL || ($import->isPublic() && !$localImport->isPublic())) {

            if ($localImport) {
                $localImport->setFile(NULL);
            }

            $this->_imports[$importPath] = $import;
            $import->setFile($this);

            return TRUE;
        }

        return FALSE;
    }

    /**
     * @return \WarriorXK\PHPProtoGen\Import[]
     */
    public function getImports() : array {
        return $this->_imports;
    }

    /**
     * @param \WarriorXK\PHPProtoGen\Message $message
     */
    public function addMessage(Message $message) {

        $messageName = $message->getName();
        if (isset($this->_messages[$messageName])) {
            throw new \LogicException('A message with name "' . $messageName . '" already exists in file "' . $this->getPath() . '"');
        }

        $this->_messages[$messageName] = $message;
        $message->setFile($this);

    }

    /**
     * @param \WarriorXK\PHPProtoGen\Enum $enum
     */
    public function addEnum(Enum $enum) {

        $name = $enum->getName();
        if (isset($this->_enums[$name])) {
            throw new \LogicException('An enum with name "' . $name . '" already exists in file "' . $this->getPath() . '"');
        }

        $this->_enums[$name] = $enum;
        $enum->setFile($this);

    }

    /**
     * @return string
     */
    public function getPath() : string {
        return $this->_path;
    }

    /**
     * @return string
     */
    public function exportToString() : string {

        $strClasses = [];
        $topLines = [];

        foreach ($this->_messages as $message) {

            $strClasses[] = $message->exportToString();

            if (!isset($this->getImports()['google/protobuf/any.proto'])) {

                $messageFields = $message->getFields();
                foreach ($messageFields as $messageField) {

                    $type = $messageField->getType()->getType();
                    if ($type === Utility::TYPE_ANY) {
                        $this->addImport(new Import('google/protobuf/any.proto'));
                        break;
                    }

                }

            }

        }

        foreach ($this->_enums as $enum) {
            $strClasses[] = $enum->exportToString();
        }

        $topLines[] = 'syntax = "proto' . $this->getSyntaxVersion() . '";';

        $package = $this->getPackage();
        if ($package !== NULL) {
            $topLines[] = '';
            $topLines[] = 'package ' . $package . ';';
        }

        $options = $this->getOptions();
        if (!empty($options)) {

            $topLines[] = '';

            foreach ($options as $option => $value) {
                $topLines[] = 'option ' . $option . ' = "' . $value . '";';
            }

        }

        // Empty line between imports and defines
        $imports = $this->getImports();
        if (!empty($imports)) {
            $topLines[] = '';
        }

        foreach ($imports as $import) {
            $topLines[] = $import->exportToString();
        }

        return PHP_EOL . implode(PHP_EOL, $topLines) . PHP_EOL . PHP_EOL . implode(PHP_EOL . PHP_EOL, $strClasses) . PHP_EOL;
    }
}
