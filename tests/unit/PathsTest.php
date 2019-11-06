<?php


use Futape\Utility\Filesystem\Paths;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Utility\Filesystem\Paths
 */
class PathsTest extends TestCase
{
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
}
