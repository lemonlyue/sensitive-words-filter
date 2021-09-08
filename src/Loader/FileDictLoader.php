<?php


namespace Lemonlyue\SensitiveWordsFilter\Loader;

use Lemonlyue\SensitiveWordsFilter\Exceptions\FileException;
use Lemonlyue\SensitiveWordsFilter\Utils\File;

/**
 * Class FileDictLoader
 * @package Lemonlyue\SensitiveWordsFilter
 */
class FileDictLoader implements DictLoaderInterface
{
    /** @var array $dict */
    protected $dict;

    /** @var string $path */
    protected $path;

    /** @var File $file */
    protected $file;

    /**
     * FileDictLoader constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * load dict.
     *
     * @return mixed|void
     * @throws FileException
     */
    public function loadDict()
    {
        $file = $this->getFile($this->path);
        $this->dict = $file->getContent();
        return true;
    }

    /**
     * get dict.
     *
     * @return mixed|void
     */
    public function getDict()
    {
        return $this->dict;
    }

    /**
     * @param $path
     * @return File
     */
    public function getFile($path)
    {
        return new File($path);
    }
}