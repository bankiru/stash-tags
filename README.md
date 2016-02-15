Stash\Item with tags support
============================

[![GitHub license](https://img.shields.io/github/license/bankiru/stash-tags.svg?style=flat-square)](https://github.com/bankiru/stash-tags/blob/master/LICENSE)
[![Github All Releases](https://img.shields.io/github/downloads/bankiru/stash-tags/total.svg?style=flat-square)](https://github.com/bankiru/stash-tags/releases)
[![Packagist](https://img.shields.io/packagist/dt/bankiru/stash-tags.svg?style=flat-square)](https://packagist.org/packages/bankiru/stash-tags)
[![Packagist](https://img.shields.io/packagist/v/bankiru/stash-tags.svg?style=flat-square)](https://github.com/bankiru/stash-tags/releases)
[![Packagist Pre Release](https://img.shields.io/packagist/vpre/bankiru/stash-tags.svg?style=flat-square)](https://github.com/bankiru/stash-tags)

[![Travis](https://img.shields.io/travis/bankiru/stash-tags.svg?style=flat-square)](https://travis-ci.org/bankiru/stash-tags)
[![Coveralls](https://img.shields.io/coveralls/bankiru/stash-tags.svg?style=flat-square)](https://coveralls.io/github/bankiru/stash-tags)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/bankiru/stash-tags.svg?style=flat-square)](https://scrutinizer-ci.com/g/bankiru/stash-tags/)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/22df3d3e-050e-419b-a6a0-608802b4ac68.svg?style=flat-square)](https://insight.sensiolabs.com/projects/22df3d3e-050e-419b-a6a0-608802b4ac68)
[![HHVM](https://img.shields.io/hhvm/bankiru/stash-tags.svg?style=flat-square)]()

Install
-------

```
composer require bankiru/stash-tags
```

Usage
-----

```
$pool = new Pool();
$pool->setItemClass('Bankiru\Stash\TaggedItem');
```

```
$item = $pool->getItem('my-key');
$item->setTags(['tag1', 'tag2']);
$item->save();
```

```
$item = $pool->getItem('my-key');
$tags = $item->getTags();
echo 'Tags: ', implode(',', $tags);
```
