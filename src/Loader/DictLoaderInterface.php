<?php


namespace Lemonlyue\SensitiveWordsFilter\Loader;

/**
 * Interface DictLoaderInterface
 * @package Lemonlyue\SensitiveWordsFilter
 */
interface DictLoaderInterface
{
    /**
     * load dict
     *
     * @return mixed
     */
    public function loadDict();

    /**
     * get dict
     *
     * @return mixed
     */
    public function getDict();
}