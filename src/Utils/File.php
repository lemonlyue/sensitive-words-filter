<?php

namespace Lemonlyue\SensitiveWordsFilter\Utils;


use Lemonlyue\SensitiveWordsFilter\Exceptions\FileException;

/**
 * Class File
 * @package Lemonlyue\SensitiveWordsFilter
 */
class File
{
    /** @var  */
    protected $path;

    /**
     * File constructor.
     * @param $filePath
     */
    public function __construct($filePath)
    {
        $this->path = $filePath;
    }

    /**
     * 获取txt文件每行内容
     *
     * @throws FileException
     */
    public function getLines()
    {
        if(!file_exists($this->path)){
            throw new FileException('File does not exist');
        }
        $f = fopen($this->path, 'rb');
        try {
            while ($line = fgets($f)) {
                yield $line;
            }
        } finally {
            fclose($f);
        }
    }

    /**
     * @return array
     * @throws FileException
     */
    public function getContent()
    {
        $content = [];
        foreach ($this->getLines() as $n => $line) {
            $content[] = $line;
        }
        return $content;
    }
}