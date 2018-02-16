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

    public function getTagForField(Field $field) : int;

}
