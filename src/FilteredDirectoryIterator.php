<?php


namespace Futape\Utility\Filesystem;


use DirectoryIterator;
use FilterIterator;
use SplFileInfo;
use UnexpectedValueException;

class FilteredDirectoryIterator extends FilterIterator
{
    const FILE_TYPE_FILE = 1;
    const FILE_TYPE_DIRECTORY = 1 << 1;
    const FILE_TYPE_LINK = 1 << 2;
    const FILE_TYPE_UNKNOWN = 1 << 3;

    /** @var int|null */
    protected $fileTypes;

    /** @var bool */
    protected $dotFiles = false;

    /** @var string|null */
    protected $nameGlob;

    /** @var string|null */
    protected $nameRegex;

    /**
     * @param string|string[]|string[][] $path Passed to self::normalize()
     *
     * @throws UnexpectedValueException If $path isn't valid
     *                                  {@see https://www.php.net/manual/en/directoryiterator.construct.php}
     */
    public function __construct($path)
    {
        parent::__construct(new DirectoryIterator(Paths::normalize($path)));
    }

    /**
     * @return bool
     */
    public function accept(): bool
    {
        /** @var DirectoryIterator $file */
        $file = $this->current();

        if ($this->getFileTypes() !== null && !($this->getFileTypes() & $this->getFileType($file))) {
            return false;
        }
        if (!$this->isDotFiles() && $file->isDot()) {
            return false;
        }
        if ($this->getNameGlob() !== null && !fnmatch($this->getNameGlob(), $file->getBasename())) {
            return false;
        }
        if ($this->getNameRegex() !== null && preg_match($this->getNameRegex(), $file->getBasename()) !== 1) {
            return false;
        }

        return true;
    }

    /**
     * @return int|null
     */
    public function getFileTypes(): ?int
    {
        return $this->fileTypes;
    }

    /**
     * @param int|null $fileTypes
     * @return self
     */
    public function setFileTypes(?int $fileTypes): self
    {
        $this->fileTypes = $fileTypes;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDotFiles(): bool
    {
        return $this->dotFiles;
    }

    /**
     * @param bool $dotFiles
     * @return self
     */
    public function setDotFiles(bool $dotFiles): self
    {
        $this->dotFiles = $dotFiles;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNameGlob(): ?string
    {
        return $this->nameGlob;
    }

    /**
     * @param string|null $nameGlob
     * @return self
     */
    public function setNameGlob(?string $nameGlob): self
    {
        $this->nameGlob = $nameGlob;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNameRegex(): ?string
    {
        return $this->nameRegex;
    }

    /**
     * @param string|null $nameRegex
     * @return self
     */
    public function setNameRegex(?string $nameRegex): self
    {
        $this->nameRegex = $nameRegex;

        return $this;
    }

    /**
     * @param SplFileInfo $file
     * @return int
     */
    protected function getFileType(SplFileInfo $file): int
    {
        switch (true) {
            case $file->isLink():
                return self::FILE_TYPE_LINK;

            case $file->isFile():
                return self::FILE_TYPE_FILE;

            case $file->isDir():
                return self::FILE_TYPE_DIRECTORY;

            default:
                return self::FILE_TYPE_UNKNOWN;
        }
    }
}
