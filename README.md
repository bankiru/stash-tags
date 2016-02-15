Stash\Item with tags support
============================

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
