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

    /**
     * Strips off a path from the beginning of another path
     *
     * The returned path gets normalized.
     * If $path begins with a '.' or a '..' path segment or is an absolute path, the resulting path will begin
     * with a '.' path segment (if $start matches).
     *
     * @see self::normalize()
     *
     * @param string|string[]|string[][] $path Passed to self::normalize()
     * @param string|string[]|string[][] $start Passed to self::normalize()
     * @return string
     */
    public static function strip($path, $start): string
    {
        $path = self::normalize($path);
        $start = rtrim(self::normalize($start), '/');
        $strippedPath = $path;

        if ($start == $path || $start . '/' == $path) {
            $strippedPath = '';
        } elseif (Strings::startsWith($path, $start . '/')) {
            $strippedPath = Strings::stripLeft($path, $start . '/');
            if (Strings::startsWith($path, ['./', '../', '/'])) {
                $strippedPath = './' . $strippedPath;
            }
        }

        return self::normalize($strippedPath);
    }
}
