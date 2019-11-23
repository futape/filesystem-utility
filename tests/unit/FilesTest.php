<?php


use Futape\Utility\Filesystem\Files;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Utility\Filesystem\Files
 */
class FilesTest extends TestCase
{
    public function testRemoveFile()
    {
        $path = tempnam(sys_get_temp_dir(), 'removetest');

        $this->assertTrue(Files::remove($path));
        $this->assertFileNotExists($path);

        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function testRemoveDirectory()
    {
        $path = tempnam(sys_get_temp_dir(), 'removetest');
        unlink($path);
        mkdir($path);

        $file = $path . '/foo';
        touch($file);

        $this->assertTrue(Files::remove($path));
        $this->assertFileNotExists($path);

        if (file_exists($file)) {
            unlink($file);
        }
        if (file_exists($path)) {
            rmdir($path);
        }
    }

    public function testRemoveFileSymlink()
    {
        $target = tempnam(sys_get_temp_dir(), 'removetest');
        $path = tempnam(sys_get_temp_dir(), 'removetest');

        unlink($path);
        symlink($target, $path);

        $this->assertTrue(Files::remove($path));
        $this->assertFileNotExists($path);

        if (file_exists($path)) {
            unlink($path);
        }
        unlink($target);
    }

    public function testRemoveDirectorySymlink()
    {
        $target = tempnam(sys_get_temp_dir(), 'removetest');
        unlink($target);
        mkdir($target);

        $path = tempnam(sys_get_temp_dir(), 'removetest');
        unlink($path);
        symlink($target, $path);

        $this->assertTrue(Files::remove($path));
        $this->assertFileNotExists($path);

        if (file_exists($path)) {
            if (PHP_OS_FAMILY == 'Windows') {
                rmdir($path);
            } else {
                unlink($path);
            }
        }
        rmdir($target);
    }

    public function testCleanDirectory()
    {
        $path = tempnam(sys_get_temp_dir(), 'cleandirectorytest');
        unlink($path);
        mkdir($path);

        $directory = $path . '/foo';
        mkdir($directory);

        $files = [
            $path . '/bar',
            $directory . '/bam'
        ];
        foreach ($files as $file) {
            touch($file);
        }

        $this->assertTrue(Files::cleanDirectory($path));

        $empty = true;
        foreach (new DirectoryIterator($path) as $file) {
            if ($file->isDot()) {
                continue;
            }

            $empty = false;

            break;
        }
        $this->assertTrue($empty);

        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        if (file_exists($directory)) {
            rmdir($directory);
        }
        if (file_exists($path)) {
            rmdir($path);
        }
    }
}
