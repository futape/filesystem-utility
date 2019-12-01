<?php


namespace Futape\Utility\Filesystem;

use UnexpectedValueException;

abstract class Files
{
    /**
     * Removes a file, directory or symbolic link
     *
     * Deleting an unexisting item is considered a successful removal of such.
     *
     * @param string|string[]|string[][] $path Passed to self::normalize()
     * @return bool
     */
    public static function remove($path): bool
    {
        $path = Paths::normalize($path);

        if (is_link($path)) {
            // On windows systems, symbolic links pointing to a directory needs to be removed using rmdir().
            // See https://bugs.php.net/bug.php?id=52176
            if (PHP_OS_FAMILY == 'Windows' && is_dir($path)) {
                rmdir($path);
            } else {
                unlink($path);
            }
        } else if (is_file($path)) {
            unlink($path);
        } else if (is_dir($path)) {
            if (self::cleanDirectory($path)) {
                rmdir($path);
            }
        }

        // Since file_exists() returns `false` for symbolic links to unexisting files or directories, check for links
        // explicitly
        return !file_exists($path) && !is_link($path);
    }

    /**
     * Empties a directory
     *
     * This method doesn't break if am item of the directory couldn't be removed.
     * However, its result will  be marked as a failure.
     *
     * @param string|string[]|string[][] $path Passed to self::normalize()
     * @return bool
     *
     * @throws UnexpectedValueException If $path isn't valid
     *                                  {@see https://www.php.net/manual/en/directoryiterator.construct.php}
     */
    public static function cleanDirectory($path): bool
    {
        $success = true;
        $directoryIterator = (new FilteredDirectoryIterator($path))
            ->setDotFiles(false);

        foreach ($directoryIterator as $file) {
            if (!self::remove($file->getPathname())) {
                $success = false;
            }
        }

        return $success;
    }
}
