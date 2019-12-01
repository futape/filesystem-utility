# futape/string-utility

This library offers a set of utilities for working with the filesystem.

Utility functions are implemented as static functions in abstract classes, which are never expected to be instantiated.

Moreover this library offers a `FilteredDirectoryIterator` (see below).

Most functions accept path arguments, that are supported by `Paths::normalize()` and pass them through.
The results of most functions are also processed by that function.

## Install

```bash
composer require futape/filesystem-utility
```

## Usage

### Paths

This utility class offers functions for working with paths.  
Whenever possible the this utility is completely independent of the real underlying filesystem.

```php
use Futape\Utility\Filesyste\Paths;

echo Paths::strip('/foo/bar/baz', '/foo'); // "./bar/baz"
```

### Files

A utility class for working with the filesystem.

```php
use Futape\Utility\Filesyste\Files;

touch('./foo');
var_dump(Files::remove('./foo')); // true
```

### FilteredDirectoryIterator

An iterator that iterates over the contents of a directory, that match specified criteria.

```php
use Futape\Utility\Filesyste\FilteredDirectoryIterator;

mkdir('./foo');
touch('./foo/bar');
symlink('./foo/bar', './foo/baz');
mkdir('./foo/bam');
var_dump(
    iterator_to_array(
        (new FilteredDirectoryIterator('./foo'))
            ->setDotFiles(false)
            ->setFileTypes(FilteredDirectoryIterator::FILE_TYPE_FILE | FilteredDirectoryIterator::FILE_TYPE_DIRECTORY)
    )
); // [DirectoryIterator("./foo/bar"), DirectoryIterator("./foo/bam")]
```

## Testing

The library is tested by unit tests using PHP Unit.

To execute the tests, install the composer dependencies (including the dev-dependencies), switch into the `tests`
directory and run the following command:

```bash
../vendor/bin/phpunit
```
