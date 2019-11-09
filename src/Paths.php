<?php


namespace Futape\Utility\Filesystem;

use Futape\Utility\ArrayUtility\Arrays;
use Futape\Utility\String\Strings;

abstract class Paths
{
    /** @var string|null */
    protected static $documentRoot;

    /**
     * @return string
     */
    public static function getDocumentRoot(): string
    {
        return self::$documentRoot ?? self::normalize($_SERVER['DOCUMENT_ROOT']);
    }

    /**
     * @param string|null $documentRoot
     * @return void
     */
    public static function setDocumentRoot(?string $documentRoot): void
    {
        self::$documentRoot = $documentRoot !== null ? self::normalize($documentRoot) : null;
    }

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

    /**
     * Builds a URL-path from a filesystem path
     *
     * Strips away the document root from the beginning of the path and fails with `null` if the path isn't a
     * descendant of the document root.
     * The URL path always has a leading slash and is normalized.
     * If the path points to a directory, a slash is appended to the URL path.
     * The single path segments are URL-encoded.
     *
     * @see self::normalize()
     *
     * @param $path
     * @return string|null
     */
    public static function toUrlPath($path): ?string
    {
        $path = self::normalize($path);
        $urlPath = self::strip($path, self::getDocumentRoot());

        if ($urlPath == $path) {
            // The path isn't a descendant of the document root
            return null;
        }

        // Build URL path
        if ($urlPath == '.') {
            $urlPath = '';
        }
        $urlPath = '/' . Strings::stripLeft($urlPath, './');

        // Append a slash if path points to a directory
        if (is_dir($path) && !Strings::endsWith($urlPath, '/')) {
            $urlPath .= '/';
        }

        // URL-encode path segments
        $urlPath = implode('/', array_map('rawurlencode', explode('/', $urlPath)));

        return $urlPath;
    }
}
