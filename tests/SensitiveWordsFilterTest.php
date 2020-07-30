<?php

namespace Lemonlyue\SensitiveWordsFilter\Tests;

use Lemonlyue\SensitiveWordsFilter\Exceptions\InvalidArgumentException;
use Lemonlyue\SensitiveWordsFilter\SensitiveWordsFilter;
use PHPUnit\Framework\TestCase;

class SensitiveWordsFilterTest extends TestCase
{
    public function testArrayFilter()
    {
//        $filter = new SensitiveWordsFilter([]);
//        $this->expectException(InvalidArgumentException::class);
//        $this->expectErrorMessage('Config Invalid Argument');
//        $filter->txtFilter('');
//        $this->fail('Failed to assert getWeather throw exception with invalid argument.');

        $filter = new SensitiveWordsFilter('');
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Config Invalid Argument');
        $filter->arrayFilter('');
        $this->fail('Failed to assert getWeather throw exception with invalid argument.');
    }
}