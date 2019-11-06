<?php


namespace Futape\Utility\Filesystem;

use Futape\Utility\ArrayUtility\Arrays;
use Futape\Utility\String\Strings;

abstract class Paths
{
    /**
     * Normalizes a path
     *
     * Does a similar task like realpath(), with the exception that the underlying filesystem doesn't has any impact
     * nor is it required at all.
     *
     * + Any platform-specific path separator is replaced by a '/' character
     * + Multiple subsequent '/' characters collapse to a single one
     * + '.' path segments (except for the first path segment) are removed
     * + '..' path segments (except for the first path segment) are removed together with their leading ones
     *   (if not also a '..' segment)
     * + Empty path segments in the arguments are replaced by '.'s
     *
     * Instead of returning an empty path, a '.' is returned.
     *
     * @param string|string[] ...$path A variable list of string or arrays of paths/path segments
     * @return string
     */
    public static function normalize(...$path): string
    {
        $normalized = Arrays::flatten($path);
        array_walk(
            $normalized,
            function (&$val) {
                $val = $val == '' ? '.' : $val;
            }
        );
        $normalized = implode('/', $normalized);
        $normalized = str_replace(DIRECTORY_SEPARATOR, '/', $normalized);
        $normalized = preg_replace('/\/{2,}/', '/', $normalized);
        $normalized = preg_replace('/(?<=\/)\.(?:\/|$)/', '', $normalized);

        $matches = [];

        while (
            preg_match(
                '/(?<=^|\/)(?!\.{2}\/)[^\/]+\/\.{2}(?:\/|$)/',
                $normalized,
                $matches,
                PREG_OFFSET_CAPTURE
            ) === 1
        ) {
            $normalized = Strings::supstr($normalized, $matches[0][1], strlen($matches[0][0]), false);
        }

        if ($normalized == '') {
            return '.';
        }

        return $normalized;
    }
}
