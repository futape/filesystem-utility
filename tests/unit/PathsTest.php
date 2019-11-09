<?php


use Futape\Utility\Filesystem\Paths;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Utility\Filesystem\Paths
 */
class PathsTest extends TestCase
{
    /**
     * Always ends with a slash
     *
     * @var string
     */
    protected static $documentRoot;

    public static function setUpBeforeClass(): void
    {
        $documentRoot = tempnam(sys_get_temp_dir(), 'pathtest');
        var_dump($documentRoot);
        unlink($documentRoot);
        mkdir($documentRoot);
        Paths::setDocumentRoot($documentRoot);
        self::$documentRoot = rtrim(Paths::getDocumentRoot(), '/') . '/';
    }

    public static function tearDownAfterClass(): void
    {
        rmdir(Paths::getDocumentRoot());
        Paths::setDocumentRoot(null);
        self::$documentRoot = null;
    }

    /**
     * @dataProvider normalizeDataProvider
     *
     * @param array $input
     * @param string $expected
     */
    public function testNormalize(array $input, string $expected)
    {
        $this->assertEquals($expected, Paths::normalize(...$input));
    }

    public function normalizeDataProvider(): array
    {
        return [
            'Merge arguments' => [
                ['/foo', ['bar', 'baz/'], '/bam/'],
                '/foo/bar/baz/bam/'
            ],
            'Collapse path separators' => [
                ['/foo//bar////baz//bam'],
                '/foo/bar/baz/bam'
            ],
            'Remove "."' => [
                ['./foo/./bar/.'],
                './foo/bar/'
            ],
            'Remove ".."' => [
                ['../foo/../bar/../../baz'],
                '../../baz'
            ],
            'Support empty path segments' => [
                ['', 'bar'],
                './bar'
            ],
            'Prevent empty path' => [
                ['foo/..'],
                '.'
            ]
        ];
    }

    public function testNormalizeSystemDirectorySeparator()
    {
        if (DIRECTORY_SEPARATOR == '/') {
            $this->markTestSkipped('The system directory separator must not be "/" for this test to work');
        }

        $this->assertEquals('foo/bar', Paths::normalize('foo' . DIRECTORY_SEPARATOR . 'bar'));
    }

    /**
     * @dataProvider stripDataProvider
     *
     * @param array $input
     * @param string $expected
     */
    public function testStrip(array $input, string $expected)
    {
        $this->assertEquals($expected, Paths::strip(...$input));
    }

    public function stripDataProvider(): array
    {
        return [
            'Basic usage' => [
                ['foo/bar/baz', 'foo'],
                'bar/baz'
            ],
            'Without trailing directory separator' => [
                ['foo/bar', 'foo/bar/'],
                '.'
            ],
            'Path begins with "." or ".." path segment or is absolute' => [
                ['/foo/bar/baz', '/foo'],
                './bar/baz'
            ],
        ];
    }

    public function testToUrlPath()
    {
        $this->assertEquals('/foo/bar.html', Paths::toUrlPath(self::$documentRoot . 'foo/bar.html'));
        $this->assertEquals('/fo%3F/ba%23r/ba%26z', Paths::toUrlPath(self::$documentRoot . 'fo?/ba#r/ba&z'));
        $this->assertNull(Paths::toUrlPath('/fake/foo/bar.html'));

        mkdir(self::$documentRoot . 'bar');
        $this->assertEquals('/bar/', Paths::toUrlPath(self::$documentRoot . 'bar'));
        rmdir(self::$documentRoot . 'bar');
    }
}
