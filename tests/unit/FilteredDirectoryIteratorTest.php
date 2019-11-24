<?php


use Futape\Utility\Filesystem\Files;
use Futape\Utility\Filesystem\FilteredDirectoryIterator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Utility\Filesystem\FilteredDirectoryIterator
 */
class FilteredDirectoryIteratorTest extends TestCase
{
    /**
     * Always ends with a slash
     *
     * @var string
     */
    protected static $directory;

    public static function setUpBeforeClass(): void
    {
        $directory = tempnam(sys_get_temp_dir(), 'filtereddirectoryiteratortest');
        unlink($directory);
        mkdir($directory);
        self::$directory = $directory . '/';

        touch(self::$directory . 'foo');
        mkdir(self::$directory . 'bar');
        symlink(self::$directory . 'foo', self::$directory . 'baz');
    }

    public static function tearDownAfterClass(): void
    {
        unlink(self::$directory . 'baz');
        rmdir(self::$directory . 'bar');
        unlink(self::$directory . 'foo');

        rmdir(self::$directory);
        self::$directory = null;
    }

    /**
     * @dataProvider filterDotFilesDataProvider
     *
     * @param bool $dotFiles
     * @param array $expected
     */
    public function testFilterDotFiles(bool $dotFiles, array $expected)
    {
        $iterator = (new FilteredDirectoryIterator(self::$directory))
            ->setDotFiles($dotFiles);
        $filteredFiles = [];
        /** @var DirectoryIterator $file */
        foreach ($iterator as $file) {
            $filteredFiles[] = $file->getBasename();
        }

        $this->assertEquals($expected, $filteredFiles);
    }

    public function filterDotFilesDataProvider(): array
    {
        return [
            'Include dot files' => [
                true,
                [
                    '.',
                    '..',
                    'bar',
                    'baz',
                    'foo',
                ]
            ],
            'Skip dot files' => [
                false,
                [
                    'bar',
                    'baz',
                    'foo',
                ]
            ]
        ];
    }

    /**
     * @dataProvider filterFileTypesDataProvider
     *
     * @param int $fileTypes
     * @param array $expected
     */
    public function testFilterFileTypes(int $fileTypes, array $expected)
    {
        $iterator = (new FilteredDirectoryIterator(self::$directory))
            ->setFileTypes($fileTypes);
        $filteredFiles = [];
        /** @var DirectoryIterator $file */
        foreach ($iterator as $file) {
            $filteredFiles[] = $file->getBasename();
        }

        $this->assertEquals($expected, $filteredFiles);
    }

    public function filterFileTypesDataProvider(): array
    {
        return [
            'Files' => [
                FilteredDirectoryIterator::FILE_TYPE_FILE,
                [
                    'foo'
                ]
            ],
            'Directories' => [
                FilteredDirectoryIterator::FILE_TYPE_DIRECTORY,
                [
                    'bar'
                ]
            ],
            'Symlinks' => [
                FilteredDirectoryIterator::FILE_TYPE_LINK,
                [
                    'baz'
                ]
            ]
        ];
    }

    public function testFilterNameRegex()
    {
        $iterator = (new FilteredDirectoryIterator(self::$directory))
            ->setNameRegex('/^f/');
        $filteredFiles = [];
        /** @var DirectoryIterator $file */
        foreach ($iterator as $file) {
            $filteredFiles[] = $file->getBasename();
        }

        $this->assertEquals(
            [
                'foo'
            ],
            $filteredFiles
        );
    }

    public function testFilterNameGlob()
    {
        $iterator = (new FilteredDirectoryIterator(self::$directory))
            ->setNameGlob('f?o');
        $filteredFiles = [];
        /** @var DirectoryIterator $file */
        foreach ($iterator as $file) {
            $filteredFiles[] = $file->getBasename();
        }

        $this->assertEquals(
            [
                'foo'
            ],
            $filteredFiles
        );
    }
}
