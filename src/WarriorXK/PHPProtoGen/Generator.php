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
     * @var string
     */
    protected $_singleImportFileName = '';

    /**
     * @var \WarriorXK\PHPProtoGen\ITagGenerator|null
     */
    protected $_tagGenerator = NULL;

    /**
     * If we should generate a single file which imports everything with 'import public' to prevent recursion
     *
     * @var bool
     */
    protected $_singleImport = FALSE;

    /**
     * @var \WarriorXK\PHPProtoGen\File[]
     */
    protected $_files = [];

    public function __construct(ITagGenerator $tagGenerator = NULL) {
        $this->setTagGenerator($tagGenerator);
    }

    /**
     * @param \WarriorXK\PHPProtoGen\ITagGenerator|null $tagGenerator
     */
    public function setTagGenerator(ITagGenerator $tagGenerator = NULL) {
        $this->_tagGenerator = $tagGenerator;
    }

    /**
     * @return \WarriorXK\PHPProtoGen\ITagGenerator|null
     */
    public function getTagGenerator() {
        return $this->_tagGenerator;
    }

    /**
     * @param \WarriorXK\PHPProtoGen\File $file
     *
     * @throws \LogicException
     */
    public function addFile(File $file) {

        $filePath = $file->getPath();
        if (isset($this->_files[$filePath])) {
            throw new \LogicException('The path "' . $filePath . '" is already in use');
        }

        $this->_files[$filePath] = $file;

        $file->setGenerator($this);

    }

    /**
     * @param bool $singleImport
     *
     * @return void
     */
    public function setUseSingleImport(bool $singleImport) {
        $this->_singleImport = $singleImport;
    }

    /**
     * @return bool
     */
    public function usesSingleImport() {
        return $this->_singleImport;
    }

    /**
     * @param string $filename
     *
     * @return void
     */
    public function setSingleImportFileName(string $filename) {
        $this->_singleImportFileName = $filename;
    }

    /**
     * @return string
     */
    public function getSingleImportFileName() : string {
        return $this->_singleImportFileName;
    }

    /**
     * @param string $path
     *
     * @return \WarriorXK\PHPProtoGen\File|null
     */
    public function getFile(string $path) {
        return $this->_files[$path] ?? NULL;
    }

    /**
     * @param \WarriorXK\PHPProtoGen\Field $field
     *
     * @return int|null
     */
    public function getTagForField(Field $field) {

        if ($this->_tagGenerator === NULL) {
            return NULL;
        }

        return $this->_tagGenerator->getTagForField($field);
    }

    public function exportToDir(string $dir) {

        if (!is_dir($dir)) {
            throw new \RuntimeException('The provided path "' . $dir . '" is not a directory!');
        }
        if (empty($this->_files)) {
            throw new \LogicException('Add files before exporting');
        }

        $usesSingleImport = $this->usesSingleImport();
        $singleImportFileName = $this->getSingleImportFileName();

        if ($usesSingleImport && !$singleImportFileName) {
            throw new \LogicException('Unable to use single import file without the filename being set!');
        }

        foreach ($this->_files as $file) {
            file_put_contents($dir . DIRECTORY_SEPARATOR . $file->getPath(), $file->exportToString());
        }

        if ($usesSingleImport) {

            /** @var \WarriorXK\PHPProtoGen\Import[] $uniqueImports */
            $uniqueImports = [];

            if (!$singleImportFileName) {
                throw new \LogicException('Unable to use single import file without the filename being set!');
            }

            $importFile = new File($singleImportFileName);
            $importFile->setGenerator($this);

            foreach ($this->_files as $file) {

                foreach ($file->getImports() as $import) {

                    $path = $import->getPath();
                    if (isset($uniqueImports[$path])) {
                        continue;
                    }

                    $import = new Import($path, TRUE);
                    $uniqueImports[$path] = TRUE;

                    $importFile->addImport($import);

                }

            }

            file_put_contents($dir . DIRECTORY_SEPARATOR . $importFile->getPath(), $importFile->exportToString(TRUE));

        }

    }
}
