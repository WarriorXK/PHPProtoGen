<?php
/**
 * Created by PhpStorm.
 * User: kevinmeijer
 * Date: 16/02/2018
 * Time: 12:03
 */
declare(strict_types = 1);

namespace WarriorXK\PHPProtoGen;

interface ITagGenerator {

    /**
     * @param \WarriorXK\PHPProtoGen\Field $field
     *
     * @return int|null
     */
    public function getTagForField(Field $field);

}
