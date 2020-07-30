<h1 align="center"> sensitive-words-filter </h1>

<p align="center"> 基于PHP敏感词过滤</p>

![](https://img.shields.io/travis/com/lemonlyue/sensitive-words-filter)
![](https://img.shields.io/github/v/tag/lemonlyue/sensitive-words-filter)

## 安装

```shell
$ composer require lemonlyue/sensitive-words-filter -vvv
```

## 使用

### 数组方式
```php
require __DIR__.'/vendor/autoload.php';

$arr = [
    '傻逼',
    '滚滚滚'
];
$filter = new \Lemonlyue\SensitiveWordsFilter\SensitiveWordsFilter($arr);
echo $filter->arrayFilter('傻逼.', 'middle');
```

### txt文本方式
```php
require __DIR__.'/vendor/autoload.php';

$filter = new \Lemonlyue\SensitiveWordsFilter\SensitiveWordsFilter('test.txt');
echo $filter->txtFilter('傻逼.');
```

test.txt
```txt
傻逼
滚滚滚
```

## 参数说明
`arrayFilter`、`txtFilter`方法参数相同，参数如下表：

|  参数 | 类型 | 默认值 | 可选值 | 说明 |
| ----  | ---- | --- | --- | --- |
|  str  | string | | |需要进行敏感词过滤的字符串 |
| level | string | high | high middle low |过滤等级：high级别只要顺序包含都屏蔽,middle级别中间间隔skipDistance个字符就屏蔽,low级别全词匹配即屏蔽 |
| skipDistance | int | 4 | | 允许敏感词跳过的最大距离，如笨aa蛋a傻瓜等等 |
| isReplace | bool | true | | 是否需要替换，不需要的话，返回是否有敏感词，否则返回被替换的字符串 |
| replace | string | * | | 替换字符 |

## 参考
https://segmentfault.com/a/1190000019137933

## License

MIT