<?php
/**
 * Created by PhpStorm.
 * User: kevinmeijer
 * Date: 15/02/2018
 * Time: 16:21
 */
declare(strict_types = 1);

namespace WarriorXK\PHPProtoGen;

class Utility {

    const T_BOOL = 'boolean',
          T_INT = 'integer',
          T_FLOAT = 'double',
          T_STRING = 'string',
          T_ARRAY = 'array',
          T_OBJECT = 'object',
          T_RESOURCE = 'resources',
          T_RESOURCE_CLOSED = 'resource (closed)',
          T_NULL = 'NULL',
          T_UNKNOWN = 'unknown type';

    const TYPE_ANY = 'google.protobuf.Any';

    public function PHPTypeToProtoType(string $type) : string {

        switch ($type) {
            case static::T_BOOL:
                return 'bool';
            case static::T_INT:
                return 'int64';
            case static::T_FLOAT:
                return 'float';
            case static::T_STRING:
                return 'string';
            case static::T_ARRAY:
                throw new \Exception('Arrays have no direct counterpart in proto!');
        }

    }
}
