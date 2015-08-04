<?php
namespace Bankiru\Stash\tests;

use Bankiru\Stash\TaggedItem;
use Stash\Driver\Ephemeral;
use Stash\Interfaces\DriverInterface;
use Stash\Interfaces\ItemInterface;
use Stash\Pool;

/**
 * Class TaggedItemTest.
 */
class TaggedItemTest extends \PHPUnit_Framework_TestCase
{
    /** @var DriverInterface */
    protected $driver;

    /** @var Pool */
    protected $pool;

    protected function setUp()
    {
        $this->driver = new Ephemeral([]);
        $this->pool = new Pool();
        $this->pool->setDriver($this->driver);
        $this->pool->setItemClass('Bankiru\Stash\TaggedItem');
    }

    /**
     * @param array $key
     * @param string $itemClass
     * @return TaggedItem|ItemInterface
     */
    public function testConstruct($key = ['test-key'], $itemClass = null)
    {
        if ($itemClass === null) {
            $item = $this->pool->getItem($key);
            static::assertInstanceOf('Bankiru\Stash\TaggedItem', $item, 'Test object is an instance of Bankiru\Stash\TaggedItem');
            static::assertInstanceOf('Stash\Interfaces\ItemInterface', $item, 'Test object is an instance of Stash\Interfaces\ItemInterface');
        } else {
            $key = (array)$key;
            array_unshift($key, 'stash_default');

            /** @var TaggedItem|ItemInterface $item */
            $item = new $itemClass();
            static::assertInstanceOf($itemClass, $item, 'Test object is an instance of ' . $itemClass);
            static::assertInstanceOf('Stash\Interfaces\ItemInterface', $item, 'Test object is an instance of Stash\Interfaces\ItemInterface');

            $item->setPool($this->pool);
            $item->setKey($key);
        }

        return $item;
    }

    public function testSetWithTags()
    {
        $value = uniqid('test-value-', true);
        $key   = ['base', 'key'];
        $stash = $this->testConstruct($key);
        static::assertAttributeInternalType('string', 'keyString', $stash, 'Argument based keys setup keystring');
        static::assertAttributeInternalType('array', 'key', $stash, 'Argument based keys setup key');

        static::assertTrue($stash->set($value, null, ['test-tag']), 'Driver class able to store data');

        $regularItem = $this->testConstruct($key, 'Stash\Item');

        $data = $regularItem->get();
        static::assertInternalType('array', $data, 'Internal representation should be an array');
        static::assertArrayHasKey('tags', $data, 'Internal representation should contain tags element');
        static::assertEquals(['test-tag'], array_keys($data['tags']), 'Tags array are not equal');
    }

    /**
     * @depends testSetWithTags
     */
    public function testGetWithTags()
    {
        $value = uniqid('test-value-', true);
        $key   = ['base', 'key'];
        $tags  = ['test-tag-1', 'test-tag-2'];
        $stash = $this->testConstruct($key);

        static::assertTrue($stash->set($value, null, $tags), 'Driver class able to store data');

        $stash = $this->testConstruct($key);
        static::assertFalse($stash->isMiss());
        static::assertEquals($value, $stash->get());
    }

    /**
     * @depends testGetWithTags
     */
    public function testGetWithNonexistentTag()
    {
        $key   = ['base', 'key'];
        $tags  = ['test-tag-1', 'test-tag-2'];
        $stash = $this->testConstruct($key);

        static::assertTrue($stash->set(uniqid('test-value-', true), null, $tags), 'Driver class able to store data');

        $tagToInvalidate = ['Bankiru\Stash\TaggedItem', '1.0', $tags[0]];

        $tagItem = $this->pool->getItem($tagToInvalidate);
        $tagItem->clear();

        $stash = $this->testConstruct($key);
        static::assertTrue($stash->isMiss());
    }

    /**
     * @depends testGetWithTags
     */
    public function testGetWithInvalidTagVersion()
    {
        $key   = ['base', 'key'];
        $tags  = ['test-tag-1', 'test-tag-2'];
        $stash = $this->testConstruct($key);

        static::assertTrue($stash->set(uniqid('test-value-', true), null, $tags), 'Driver class able to store data');

        $tagToInvalidate = ['Bankiru\Stash\TaggedItem', '1.0', $tags[0]];

        $tagItem = $this->pool->getItem($tagToInvalidate);
        $tagItem->set('INVALIDVERSION');

        $stash = $this->testConstruct($key);
        static::assertTrue($stash->isMiss());
    }

    /**
     * @depends testGetWithNonexistentTag
     */
    public function testClearByTags()
    {
        $key   = ['base', 'key'];
        $tags  = ['test-tag-1', 'test-tag-2'];
        $stash = $this->testConstruct($key);

        static::assertTrue($stash->set(uniqid('test-value-', true), null, $tags), 'Driver class able to store data');

        $stash->clearByTags($tags);

        $stash = $this->testConstruct($key);
        static::assertTrue($stash->isMiss());
    }
}
