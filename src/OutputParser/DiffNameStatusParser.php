<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\OutputParser;

use Sweetchuck\Robo\Git\ListStagedFilesItem;
use Sweetchuck\Robo\Git\OutputParserInterface;
use Webmozart\PathUtil\Path;

class DiffNameStatusParser implements OutputParserInterface
{
    /**
     * @var string
     */
    protected $filePathStyle = 'relativeToTopLevel';

    public function getFilePathStyle(): string
    {
        return $this->filePathStyle;
    }

    /**
     * @param string $value
     *   Allowed values:
     *   - relativeToTopLevel
     *   - relativeToWorkingDirectory
     *   - absolute
     *
     * @return $this
     */
    public function setFilePathStyle(string $value)
    {
        $this->filePathStyle = $value;

        return $this;
    }

    /**
     * @var string
     */
    protected $gitTopLevelDir = '';

    public function getGitTopLevelDir(): string
    {
        return $this->gitTopLevelDir;
    }

    /**
     * @return $this
     */
    public function setGitTopLevelDir(string $gitTopLevelDir)
    {
        $this->gitTopLevelDir = $gitTopLevelDir;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(int $exitCode, string $stdOutput, string $stdError): array
    {
        $items = [
            'fileNames' => [],
            'files' => [],
        ];

        $stdOutput = trim($stdOutput, "\0");
        if (!$stdOutput) {
            return $items;
        }

        $parts = explode("\0", $stdOutput);
        for ($i = 0; $i < count($parts); $i += 2) {
            if (!isset($parts[$i + 1])) {
                break;
            }

            $item = new ListStagedFilesItem();
            $item->status = $parts[$i];
            $item->fileName = $this->processFileName($parts[$i + 1]);
            $items['fileNames'][] = $item->fileName;
            $items['files'][$item->fileName] = $item;
        }

        return $items;
    }

    protected function processFileName(string $fileName): string
    {
        $filePathStyle = $this->getFilePathStyle();
        if ($filePathStyle === 'relativeToTopLevel') {
            return $fileName;
        }

        if ($filePathStyle === 'relativeToWorkingDirectory') {
            return "./$fileName";
        }

        return Path::join($this->getGitTopLevelDir(), $fileName);
    }
}
