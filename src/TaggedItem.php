<?php

namespace Bankiru\Stash;

use Stash\Item;

/**
 * Class TaggedItem.
 */
class TaggedItem extends Item
{
    const VERSION    = '0.2';
    const DATA_FIELD = '@@data@@';
    const TAGS_FIELD = '@@tags@@';

    /** @var string[] */
    private $tags = [];

    /**
     * @return string[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param string[] $tags
     *
     * @return static
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $dataBackup = $this->data;

        $this->data = [
            static::DATA_FIELD => $dataBackup,
            static::TAGS_FIELD => iterator_to_array($this->getVersionedTags($this->tags)),
        ];

        $result = parent::save();

        $this->data = $dataBackup;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        parent::get();

        if (
            is_array($this->data)
            && array_key_exists(static::DATA_FIELD, $this->data)
            && array_key_exists(static::TAGS_FIELD, $this->data)
        ) {
            $this->tags = array_keys($this->data[static::TAGS_FIELD]);
            $this->data = $this->data[static::DATA_FIELD];
        }

        return $this->data;
    }

    /**
     * @param array $tags
     *
     * @return bool
     */
    public function clearByTags(array $tags)
    {
        if (!$tags) {
            return false;
        }

        /** @var Item $tagItem */
        foreach ($this->getTagItems($tags) as $tagItem) {
            $tagItem->clear();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function validateRecord($validation, &$record)
    {
        $expiration = ($_ = &$record['expiration']);
        if (isset($record['data'], $record['data']['return'])
            && is_array($record['data']['return'])
            && isset($record['data']['return'][static::DATA_FIELD], $record['data']['return'][static::TAGS_FIELD])
            && !empty($record['data']['return'][static::TAGS_FIELD])
        ) {
            $tags = $record['data']['return'][static::TAGS_FIELD];
            foreach ($this->getTagItems(array_keys($record['data']['return'][static::TAGS_FIELD])) as $tag => $tagItem) {
                $tagVersion = $tagItem->get();
                if ($tagVersion !== $tags[$tag]) {
                    unset($record['expiration']);
                    break;
                }
            }
        }

        parent::validateRecord($validation, $record);

        if ($expiration !== null) {
            $record['expiration'] = $expiration;
        }
    }

    /**
     * Mangles the name to deny intersection of tag keys & data keys.
     * Mangled tag names are NOT saved in memcache $combined[0] value,
     * mangling is always performed on-demand (to same some space).
     *
     * @see self::mangleTag
     *
     * @param string[] $tags Tags to mangle.
     *
     * @return array List of mangled tags.
     */
    private static function mangleTags(array $tags)
    {
        return array_map(function ($tag) { return __CLASS__ . '/' . static::VERSION . '/' . $tag; }, $tags);
    }

    /**
     * Generates a new unique identifier for tag version.
     *
     * @return string Globally (hopefully) unique identifier.
     */
    private static function generateNewTagVersion()
    {
        static $counter = 0;

        return sha1(microtime() . getmypid() . uniqid('', true) . ++$counter);
    }

    /**
     * Loads tags from cache and returns them with generator.
     *
     * @param array $tags
     *
     * @return \Generator|TaggedItem[]
     */
    private function getTagItems(array $tags)
    {
        $mangledTags       = self::mangleTags($tags);
        $mangledTagsToTags = array_combine($mangledTags, $tags);
        $iterator          = $this->pool->getItems($mangledTags);
        foreach ($iterator as $mangledTag => $tagItem) {
            yield $mangledTagsToTags[$mangledTag] => $tagItem;
        }
    }

    /**
     * Loads tags versions from cache,
     * generate new versions if not found in cache
     * and returns them with generator.
     *
     * @param array $tags
     *
     * @return \Generator|string[]
     */
    private function getVersionedTags(array $tags)
    {
        /** @var Item $tagItem */
        foreach ($this->getTagItems($tags) as $tag => $tagItem) {
            $tagVersion = $tagItem->get();
            if ($tagItem->isMiss()) {
                $tagVersion = self::generateNewTagVersion();
                $tagItem->set($tagVersion)->save();
            }

            yield $tag => $tagVersion;
        }
    }
}
