<?php
/**
 * Created by PhpStorm.
 * User: kevinmeijer
 * Date: 15/02/2018
 * Time: 19:57
 */

declare(strict_types = 1);

namespace WarriorXK\PHPProtoGen;

class Import {

    /**
     * @var bool
     */
    protected $_public = FALSE;

    /**
     * @var string
     */
    protected $_path = NULL;

    /**
     * @var \WarriorXK\PHPProtoGen\File|null
     */
    protected $_file = NULL;

    public function __construct(string $path, bool $public = FALSE) {

        $this->setPublic($public);
        $this->_path = $path;

    }

    public function setFile(File $file = NULL) {
        $this->_file = $file;
    }

    public function setPublic(bool $public) {
        $this->_public = $public;
    }

    public function isPublic() : bool {
        return $this->_public;
    }

    public function getPath() : string {
        return $this->_path;
    }

    public function exportToString() : string {
        return 'import ' . ($this->isPublic() ? 'public ' : '') . $this->getPath();
    }

}
