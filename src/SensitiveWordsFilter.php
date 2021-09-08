<?php


namespace Lemonlyue\SensitiveWordsFilter;

use Lemonlyue\SensitiveWordsFilter\Exceptions\InvalidArgumentException;
use Lemonlyue\SensitiveWordsFilter\Loader\DictLoaderInterface;
use Lemonlyue\SensitiveWordsFilter\Loader\FileDictLoader;


/**
 * Class SensitiveWordsFilter
 * @package Lemonlyue\SensitiveWordsFilter
 */
class SensitiveWordsFilter
{
    /**
     * Dict loader
     *
     * @var DictLoaderInterface $loader
     */
    protected $loader;

    /**
     * @var string $config
     */
    protected $config = '';

    /**
     * @var array Sensitive word dictionary
     */
    protected $dict;

    /** @var int $level */
    protected $level = SensitiveWordConstants::LEVEL_MIDDLE;

    /** @var string $replaceString */
    protected $replaceString = '*';

    /** @var array $sensitiveWordsDict */
    protected $sensitiveWordsDict;

    /**
     * SensitiveWordsFilter constructor.
     * @param string $config
     * @param string $loaderName
     */
    public function __construct($config = '', $loaderName = '')
    {
        $this->loader = $loaderName ?: FileDictLoader::class;
        $this->config = $config;
    }

    /**
     * Return dict loader
     *
     * @return DictLoaderInterface|mixed
     * @throws InvalidArgumentException
     */
    public function getLoader()
    {
        if (!file_exists($this->loader)) {
            throw new InvalidArgumentException('Invalid Loader.');
        }
        if (!($this->loader instanceof DictLoaderInterface)) {
            $loaderName = $this->loader;
            if (empty($this->config)) {
                throw new InvalidArgumentException('Invalid Config.');
            }
            $this->loader = new $loaderName($this->config);
        }

        return $this->loader;
    }

    /**
     * Loader setter
     *
     * @param DictLoaderInterface $loader
     * @return $this
     */
    public function setLoader(DictLoaderInterface $loader)
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * convert
     *
     * @param string $string
     * @param int $skipDistance
     * @return string
     * @throws InvalidArgumentException
     */
    public function convert($string, $skipDistance = 4)
    {
        if (empty($this->sensitiveWordsDict)) {
            $this->buildDict();
        }
        return $this->filter($string, $this->level, $skipDistance, true, $this->replaceString);
    }

    /**
     * has sensitive words.
     *
     * @param $string
     * @return bool
     */
    public function hasSensitiveWords($string)
    {
        return $this->filter($string, $this->level, 0, true, $this->replaceString);
    }

    /**
     * build dict.
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function buildDict()
    {
        /** @var DictLoaderInterface $loader */
        $loader = $this->getLoader();
        $loader->loadDict();
        $dict = $loader->getDict();
        return $this->build($dict);
    }

    /**
     * build sensitive words dict.
     *
     * @param array $dict
     * @return array
     */
    protected function build($dict)
    {
        foreach ($dict as $value) {
            $this->addWords(trim($value));
        }
        return $this->dict;
    }

    /**
     * sensitive words dict setter.
     *
     * @param $dict
     */
    public function setSensitiveWordsDict($dict)
    {
        $this->sensitiveWordsDict = $dict;
    }

    /**
     * level setter
     *
     * @param $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * replace string setter
     *
     * @param $string
     */
    public function setReplaceString($string)
    {
        $this->replaceString = $string;
    }

    /**
     * add sensitive words to node.
     *
     * @param string $word
     */
    protected function addWords($word)
    {
        $wordArr = $this->splitStr($word);
        $curNode = &$this->dict;
        foreach ($wordArr as $char) {
            if (!isset($curNode)) {
                $curNode[$char] = [];
            }
            $curNode = &$curNode[$char];
        }
        // The node at the end of
        $curNode['end'] = true;
    }

    /**
     * split string.
     *
     * @param string $str
     * @return array|false|string[]
     */
    protected function splitStr($str)
    {
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * @desc Sensitive word filtering
     * @param string $str String to be verified
     * @param string $level High as long as sequence contains all shielding | skipDistance characters among middle interval would block | low whole word matching the shielding
     * @param int $skipDistance The maximum distance that sensitive words are allowed to skip
     * @param bool $isReplace Whether to replace, if not, returns whether there are sensitive words, otherwise returns the replaced string
     * @param string $replace Substitution characters
     * @return bool|string
     */
    public function filter($str, $level, $skipDistance, $isReplace, $replace)
    {
        // Maximum distance allowed to jump
        switch ($level) {
            case SensitiveWordConstants::LEVEL_HIGHT:
                $maxDistance = strlen($str) + 1;
                break;
            case SensitiveWordConstants::LEVEL_MIDDLE:
                $maxDistance = max($skipDistance, 0) + 1;
                break;
            default:
                $maxDistance = 1;
                break;
        }
        $strArr = $this->splitStr($str);
        $strLength = count($strArr);
        $isSensitive = false;
        foreach ($strArr as $i => $iValue) {
            // Check whether the current sensitive word has corresponding nodes
            $curChar = $iValue;
            if (!isset($this->dict[$curChar])) {
                continue;
            }
            $curNode = &$this->dict[$curChar];
            $dist = 0;
            $matchIndex = [$i];
            // Matches whether the following string matches the remaining sensitive words
            for ($j = $i + 1; $j < $strLength && $dist < $maxDistance; $j++) {
                if (!isset($curNode[$strArr[$j]])) {
                    $dist++;
                    continue;
                }
                // If a match is found, the corresponding character location is stored for subsequent substitution of sensitive words
                $matchIndex[] = $j;
                $curNode = &$curNode[$strArr[$j]];
            }

            // Determine if you have reached the end of the sensitive word dictionary. If so, replace the sensitive word
            if (isset($curNode['end']) && $isReplace) {
                $isSensitive = true;
                foreach ($matchIndex as $index) {
                    $strArr[$index] = $replace;
                }
            }
        }
        if ($isReplace) {
            return implode('', $strArr);
        }

        return $isSensitive;
    }
}