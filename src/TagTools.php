<?php
namespace Bankiru\Stash;

use Stash\Interfaces\ItemInterface;
use Stash\Item;

/**
 * Class TagTools.
 */
trait TagTools
{
    /**
     * Mangles the name to deny intersection of tag keys & data keys.
     * Mangled tag names are NOT saved in memcache $combined[0] value,
     * mangling is always performed on-demand (to same some space).
     *
     * @param string $tag Tag name to mangle.
     *
     * @return string Mangled tag name.
     */
    private function mangleTag($tag)
    {
        return [__CLASS__, '1.0', $tag];
    }

    /**
     * The same as mangleTag(), but mangles a list of tags.
     *
     * @see self::mangleTag
     *
     * @param array $tags Tags to mangle.
     *
     * @return array List of mangled tags.
     */
    private function mangleTags(array $tags)
    {
        return array_map([$this, 'mangleTag'], $tags);
    }

    /**
     * Generates a new unique identifier for tag version.
     *
     * @return string Globally (hopefully) unique identifier.
     */
    private function generateNewTagVersion()
    {
        static $counter = 0;

        return sha1(microtime() . getmypid() . uniqid('', true) . ++$counter);
    }

    /**
     * Loads tags from cache and returns them with generator.
     *
     * @param array $tags
     *
     * @return \Generator|ItemInterface[]
     */
    private function getTags(array $tags)
    {
        $mangledTags       = $this->mangleTags($tags);
        $mangledTagsToTags = array_combine(array_map(function($mangledTag){ return implode('/', $mangledTag); }, $mangledTags), $tags);
        /* @var Item $tagItem */
        $iterator = $this->pool->getItemIterator($mangledTags);
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
     * @return \Generator|string[]
     */
    private function getVersionedTags(array $tags)
    {
        /** @var Item $tagItem */
        foreach ($this->getTags($tags) as $tag => $tagItem) {
            $tagVersion = $tagItem->get();
            if ($tagItem->isMiss()) {
                $tagVersion = $this->generateNewTagVersion();
                $tagItem->set($tagVersion);
            }

            yield $tag => $tagVersion;
        }
    }
}
