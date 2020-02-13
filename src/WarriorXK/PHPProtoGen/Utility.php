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
          TYPE_FLOAT = 'float',
          TYPE_DOUBLE = 'double';

    const MESSAGE_ANY = 'google.protobuf.Any',
          MESSAGE_STRUCT = 'google.protobuf.Struct',
          MESSAGE_VALUE = 'google.protobuf.Value';

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

    /**
     * Converts an array of integers to an array of ranges, so [1, 2, 3, 9, 10, 15] becomes [[min => 1, max=> 3], [min => 9, max => 10], [min => 15, max => 15]]
     *
     * @param int[] $ints
     *
     * @return int[][]
     */
    public static function RangesFromInts(array $ints) : array {

        $ranges = [];

        sort($ints);

        $currentMax = NULL;
        $currentMin = NULL;

        foreach ($ints as $int) {

            if (!is_int($int)) {
                throw new \InvalidArgumentException('Only integers are allowed');
            }

            if ($currentMin === NULL) { // We are starting a new range

                $currentMax = $int;
                $currentMin = $int;

                continue;
            }

            if ($int === $currentMax) { // Double occurance of the same value, allowed
                continue;
            }

            if ($int === ($currentMax + 1)) { // Next digit in the current range
                $currentMax = $int;
                continue;
            }

            // We've reached the end of the current range
            $ranges[] = [
                'min' => $currentMin,
                'max' => $currentMax,
            ];

            $currentMin = $int;
            $currentMax = $int;

        }

        if ($currentMin !== NULL) {

            $ranges[] = [
                'min' => $currentMin,
                'max' => $currentMax,
            ];

        }

        return $ranges;
    }

    /**
     * @param string $comment
     * @param int    $indentationLevel
     *
     * @return string
     */
    public static function GenerateComment(string $comment, int $indentationLevel = 0, bool $forceMultiLine = FALSE) : string {

        $in = str_repeat('    ', $indentationLevel);

        $commentsStr = '';

        if (strlen($comment) > 0) {

            $commentLines = explode(PHP_EOL, $comment);
            if (count($commentLines) === 1 && !$forceMultiLine) {
                $commentsStr = $in . '// ' . $commentLines[0];
            } else {

                $commentsStr = $in . '/*' . PHP_EOL;
                foreach ($commentLines as $line) {
                    $commentsStr .= $in . ' * ' . $line . PHP_EOL;
                }
                $commentsStr .= $in . ' */';

            }

        }

        return $commentsStr;
    }
}
