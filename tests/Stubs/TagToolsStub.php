<?php
namespace Bankiru\Stash\Tests\Stubs;

use Bankiru\Stash\TagTools;
use Stash\Interfaces\ItemInterface;
use Stash\Interfaces\PoolInterface;

class TagToolsStub
{
    use TagTools;

    /** @var PoolInterface */
    private $pool;

    /**
     * @return PoolInterface
     */
    public function getPool()
    {
        return $this->pool;
    }

    /**
     * @param PoolInterface $pool
     */
    public function setPool(PoolInterface $pool)
    {
        $this->pool = $pool;
    }

    /**
     * Mangles the name to deny intersection of tag keys & data keys.
     * Mangled tag names are NOT saved in memcache $combined[0] value,
     * mangling is always performed on-demand (to same some space).
     *
     * @param string $tag Tag name to mangle.
     *
     * @return string Mangled tag name.
     */
    public function _mangleTag($tag)
    {
        return $this->mangleTag($tag);
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
    public function _mangleTags(array $tags)
    {
        return $this->mangleTags($tags);
    }

    /**
     * Generates a new unique identifier for tag version.
     *
     * @return string Globally (hopefully) unique identifier.
     */
    public function _generateNewTagVersion()
    {
        return $this->generateNewTagVersion();
    }

    /**
     * @param array $tags
     *
     * @return \Generator|ItemInterface[]
     */
    public function _getTags(array $tags)
    {
        return $this->getTags($tags);
    }

    public function _getVersionedTags(array $tags)
    {
        return $this->getVersionedTags($tags);
    }
}
