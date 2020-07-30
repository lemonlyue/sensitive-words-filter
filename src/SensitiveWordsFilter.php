<?php


namespace Lemonlyue\SensitiveWordsFilter;


use Lemonlyue\SensitiveWordsFilter\Exceptions\InvalidArgumentException;

class SensitiveWordsFilter
{
    /**
     * @var string 敏感词字典
     */
    protected $dict;

    /**
     * @var array 配置
     */
    protected $config;

    /**
     * @desc SensitiveWordsFilter constructor.
     * @param mixed $config 相关配置
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @desc 数组敏感词过滤
     * @param string $str 需要校验的字符串
     * @param string $level high 只要顺序包含都屏蔽 | middle 中间间隔skipDistance个字符就屏蔽 | low 全词匹配即屏蔽
     * @param int $skipDistance 允许敏感词跳过的最大距离，如笨aa蛋a傻瓜等等
     * @param bool $isReplace 是否需要替换，不需要的话，返回是否有敏感词，否则返回被替换的字符串
     * @param string $replace 替换字符
     * @return bool|string
     * @return bool|string
     * @throws InvalidArgumentException
     */
    public function arrayFilter($str, $level = 'high', $skipDistance = 4, $isReplace = true, $replace = '*')
    {
        $this->loadArrayData();
        return $this->filter($str, $level, $skipDistance, $isReplace, $replace);
    }

    /**
     * @desc txt文件敏感词过滤
     * @param string $str 需要校验的字符串
     * @param string $level high 只要顺序包含都屏蔽 | middle 中间间隔skipDistance个字符就屏蔽 | low 全词匹配即屏蔽
     * @param int $skipDistance 允许敏感词跳过的最大距离，如笨aa蛋a傻瓜等等
     * @param bool $isReplace 是否需要替换，不需要的话，返回是否有敏感词，否则返回被替换的字符串
     * @param string $replace 替换字符
     * @return bool|string
     * @throws Exceptions\FileException
     * @throws InvalidArgumentException
     */
    public function txtFilter($str, $level = 'high', $skipDistance = 4, $isReplace = true, $replace = '*')
    {
        $this->loadTxtFileData();
        return $this->filter($str, $level, $skipDistance, $isReplace, $replace);
    }

    /**
     * @desc 加载敏感词数组
     * @throws InvalidArgumentException
     */
    protected function loadArrayData()
    {
        if (!is_array($this->config)) {
            throw new InvalidArgumentException('Config Invalid Argument');
        }
        foreach ($this->config as $value) {
            $this->addWords(trim($value));
        }
    }

    /**
     * @throws Exceptions\FileException
     * @throws InvalidArgumentException
     */
    protected function loadTxtFileData()
    {
        if (!is_string($this->config)) {
            throw new InvalidArgumentException('Config Invalid Argument');
        }
        $file = $this->getFile($this->config);
        $arr = $file->getContent();
        foreach ($arr as $value) {
            $this->addWords(trim($value));
        }
    }

    /**
     * @param string $path
     * @return TxtFile
     */
    protected function getFile($path)
    {
        return new TxtFile($path);
    }

    /**
     * @desc 添加敏感词到节点
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
        // 敏感词节点的终点
        $curNode['end'] = true;
    }

    /**
     * @desc 切割字符串
     * @param string $str
     * @return array|false|string[]
     */
    protected function splitStr($str)
    {
        //将字符串分割成组成它的字符
        // 其中/u 表示按unicode(utf-8)匹配（主要针对多字节比如汉字），否则默认按照ascii码容易出现乱码
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * @desc 敏感词过滤
     * @param string $str 需要校验的字符串
     * @param string $level high 只要顺序包含都屏蔽 | middle 中间间隔skipDistance个字符就屏蔽 | low 全词匹配即屏蔽
     * @param int $skipDistance 允许敏感词跳过的最大距离，如笨aa蛋a傻瓜等等
     * @param bool $isReplace 是否需要替换，不需要的话，返回是否有敏感词，否则返回被替换的字符串
     * @param string $replace 替换字符
     * @return bool|string
     */
    protected function filter($str, $level = 'high', $skipDistance = 4, $isReplace = true, $replace = '*')
    {
        //允许跳过的最大距离
        if ($level === 'high') {
            $maxDistance = strlen($str) + 1;
        } elseif ($level === 'middle') {
            $maxDistance = max($skipDistance, 0) + 1;
        } else {
            $maxDistance = 2;
        }
        $strArr = $this->splitStr($str);
        $strLength = count($strArr);
        $isSensitive = false;
        for ($i = 0; $i < $strLength; $i++) {
            //判断当前敏感字是否有存在对应节点
            $curChar = $strArr[$i];
            if (!isset($this->dict[$curChar])) {
                continue;
            }
            $isSensitive = true; //引用匹配到的敏感词节点
            $curNode = &$this->dict[$curChar];
            $dist = 0;
            $matchIndex = [$i]; //匹配后续字符串是否match剩余敏感词
            for ($j = $i + 1; $j < $strLength && $dist < $maxDistance; $j++) {
                if (!isset($curNode[$strArr[$j]])) {
                    $dist++; continue;
                }
                //如果匹配到的话，则把对应的字符所在位置存储起来，便于后续敏感词替换
                $matchIndex[] = $j;
                //继续引用
                $curNode = &$curNode[$strArr[$j]];
            }

            //判断是否已经到敏感词字典结尾，是的话，进行敏感词替换
            if (isset($curNode['end']) && $isReplace) {
                foreach ($matchIndex as $index) {
                    $strArr[$index] = $replace;
                }
                $i = max($matchIndex);
            }
        }
        if ($isReplace) {
            return implode('', $strArr);
        }

        return $isSensitive;
    }
}