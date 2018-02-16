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
     * @var \WarriorXK\PHPProtoGen\Import[]
     */
    protected $_imports = [];

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
     * @return string
     */
    public function getPath() : string {
        return $this->_path;
    }

    /**
     * @return string
     */
    public function exportToString() : string {

        $strMessages = [];
        $topLines = [];

        if (!isset($this->getImports()['google/protobuf/any.proto'])) {

            foreach ($this->_messages as $message) {

                $strMessages[] = $message->exportToString();

                $messageFields = $message->getFields();
                foreach ($messageFields as $messageField) {

                    $type = $messageField->getType()->getType();
                    if ($type === Utility::TYPE_ANY) {
                        $this->addImport(new Import('google/protobuf/any.proto'));
                        break 2;
                    }

                }

            }

        }

        $imports = $this->getImports();
        foreach ($imports as $import) {
            $topLines[] = $import->exportToString();
        }

        // Empty line between imports and defines
        if (!empty($imports)) {
            $topLines[] = '';
        }

        $topLines[] = 'syntax = "proto' . $this->getSyntaxVersion() . '";';

        return PHP_EOL . implode(PHP_EOL, $topLines) . PHP_EOL . PHP_EOL . implode(PHP_EOL . PHP_EOL, $strMessages) . PHP_EOL;
    }

}
