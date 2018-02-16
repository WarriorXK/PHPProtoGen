<?php
/**
 * Created by PhpStorm.
 * User: kevinmeijer
 * Date: 15/02/2018
 * Time: 14:41
 */
declare(strict_types = 1);

namespace WarriorXK\PHPProtoGen;

class Generator {

    /**
     * @var \WarriorXK\PHPProtoGen\ITagGenerator
     */
    protected $_tagGenerator = NULL;

    /**
     * The root file is the file from which we include the others
     *
     * @var \WarriorXK\PHPProtoGen\File
     */
    protected $_rootFile = NULL;

    /**
     * @var \WarriorXK\PHPProtoGen\File[]
     */
    protected $_files = [];

    public function __construct(ITagGenerator $tagGenerator) {
        $this->setTagGenerator($tagGenerator);
    }

    public function setTagGenerator(ITagGenerator $tagGenerator) {
        $this->_tagGenerator = $tagGenerator;
    }

    public function getTagGenerator() : ITagGenerator {
        return $this->_tagGenerator;
    }

    /**
     * @param \WarriorXK\PHPProtoGen\File $file
     * @param bool                        $setAsRoot
     *
     * @throws \LogicException
     */
    public function addFile(File $file, bool $setAsRoot = FALSE) {

        $filePath = $file->getPath();
        if (isset($this->_files[$filePath])) {
            throw new \LogicException('The path "' . $filePath . '" is already in use');
        }

        $this->_files[$filePath] = $file;

        if ($setAsRoot || $this->_rootFile === NULL) {
            $this->_rootFile = $file;
        }

        $file->setGenerator($this);

    }

    /**
     * @param string $path
     *
     * @return \WarriorXK\PHPProtoGen\File|null
     */
    public function getFile(string $path) {
        return $this->_files[$path] ?? NULL;
    }

    public function getTagForField(Field $field) : int {
        return $this->_tagGenerator->getTagForField($field);
    }

    public function exportToDir(string $dir) {

        if (!is_dir($dir)) {
            throw new \RuntimeException('The provided path "' . $dir . '" is not a directory!');
        }
        if (empty($this->_files)) {
            throw new \LogicException('Add files before exporting');
        }

        foreach ($this->_files as $file) {
            file_put_contents($dir . DIRECTORY_SEPARATOR . $file->getPath(), $file->exportToString());
        }

    }
}
