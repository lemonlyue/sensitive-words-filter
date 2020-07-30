<?php


namespace Lemonlyue\SensitiveWordsFilter;


use Lemonlyue\SensitiveWordsFilter\Exceptions\FileException;

class TxtFile
{
    protected $path;

    public function __construct($filePath)
    {
        $this->path = $filePath;
    }

    /**
     * @desc 获取txt文件每行内容
     * @throws FileException
     */
    public function getLines()
    {
        if(!file_exists($this->path)){
            throw new FileException('File does not exist');
        }
        $f = fopen($this->path, 'r');
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