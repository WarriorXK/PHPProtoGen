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

    const TYPE_MAP = 'map',
          TYPE_INT32 = 'int32',
          TYPE_SINT32 = 'sint32',
          TYPE_UINT32 = 'uint32',
          TYPE_INT64 = 'int64',
          TYPE_SINT64 = 'sint64',
          TYPE_UINT64 = 'uint64',
          TYPE_STRING = 'string',
          TYPE_BYTES = 'bytes',
          TYPE_BOOL = 'bool',
          TYPE_FLOAT = 'float';

    const MESSAGE_ANY = 'google.protobuf.Any';

    /**
     * @param string $type
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function PHPTypeToProtoType(string $type) : string {

        switch ($type) {
            case static::T_BOOL:
                return self::TYPE_BOOL;
            case static::T_INT:
                return self::TYPE_INT64;
            case static::T_FLOAT:
                return self::TYPE_FLOAT;
            case static::T_STRING:
                return self::TYPE_STRING;
            case static::T_ARRAY:
                throw new \Exception('Arrays have no direct counterpart in proto!');
            default:
                throw new \Exception('Unhandled type: ' . $type);
        }

    }
}
