<?php

namespace Bankiru\Stash\tests;

use Bankiru\Stash\TaggedItem;
use Stash\Driver\Ephemeral;
use Stash\Interfaces\DriverInterface;
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
        $this->pool   = new Pool();
        $this->pool->setDriver($this->driver);
        $this->pool->setItemClass('Bankiru\Stash\TaggedItem');
    }

    public function testConstruct()
    {
        $item = $this->pool->getItem('test-key');
        static::assertInstanceOf('Bankiru\Stash\TaggedItem', $item, 'Test object is an instance of Bankiru\Stash\TaggedItem');
        static::assertInstanceOf('Stash\Interfaces\ItemInterface', $item, 'Test object is an instance of Stash\Interfaces\ItemInterface');
    }

    /**
     * @covers Bankiru\Stash\TaggedItem::mangleTags
     */
    public function testMangleTag()
    {
        $tags = [
            uniqid('test-tag-', true),
            uniqid('test-tag-', true),
        ];

        $expectedMangledTags = [
            'Bankiru\Stash\TaggedItem/' . TaggedItem::VERSION . '/' . $tags[0],
            'Bankiru\Stash\TaggedItem/' . TaggedItem::VERSION . '/' . $tags[1],
        ];

        $refMethod = (new \ReflectionClass('Bankiru\Stash\TaggedItem'))->getMethod('mangleTags');
        $refMethod->setAccessible(true);
        $mangledTags = $refMethod->invoke(null, $tags);

        static::assertInternalType('array', $mangledTags);
        static::assertEquals($expectedMangledTags, $mangledTags);
    }

    /**
     * @covers Bankiru\Stash\TaggedItem::generateNewTagVersion
     */
    public function testGenerateNewTagVersion()
    {
        $refMethod = (new \ReflectionClass('Bankiru\Stash\TaggedItem'))->getMethod('generateNewTagVersion');
        $refMethod->setAccessible(true);

        $version1 = $refMethod->invoke(null);
        static::assertRegExp('@^[0-9a-f]{40}$@', $version1);

        $version2 = $refMethod->invoke(null);
        static::assertRegExp('@^[0-9a-f]{40}$@', $version2);

        static::assertNotEquals($version1, $version2);
    }

    /**
     * @covers Bankiru\Stash\TaggedItem::getTagItems
     */
    public function testGetTagItems()
    {
        $key  = uniqid('test/key/', true);
        $tags = [
            uniqid('test-tag-', true),
            uniqid('test-tag-', true),
        ];

        /** @var TaggedItem $item */
        $item = $this->pool->getItem($key);

        $refMethod = (new \ReflectionObject($item))->getMethod('getTagItems');
        $refMethod->setAccessible(true);

        $generator = $refMethod->invoke($item, $tags);
        static::assertInstanceOf('Generator', $generator);

        $count = 0;
        foreach ($generator as $key => $value) {
            ++$count;
            static::assertInternalType('string', $key);
            static::assertInstanceOf('Stash\Item', $value);
            static::assertTrue($value->isMiss());
        }

        static::assertEquals(count($tags), $count);
    }

    /**
     * @covers Bankiru\Stash\TaggedItem::getVersionedTags
     * @depends testGenerateNewTagVersion
     * @depends testGetTagItems
     */
    public function testGetVersionedTags()
    {
        $key  = uniqid('test/key/', true);
        $tags = [
            uniqid('test-tag-', true),
            uniqid('test-tag-', true),
        ];

        /** @var TaggedItem $item */
        $item = $this->pool->getItem($key);

        $refMethod = (new \ReflectionObject($item))->getMethod('getVersionedTags');
        $refMethod->setAccessible(true);

        $generator = $refMethod->invoke($item, $tags);
        static::assertInstanceOf('Generator', $generator);

        $count = 0;
        foreach ($generator as $key => $value) {
            ++$count;
            static::assertInternalType('string', $key);
            static::assertInternalType('string', $value);
            static::assertRegExp('@^[0-9a-f]{40}$@', $value);
        }

        static::assertEquals(count($tags), $count);
    }

    /**
     * @depends testConstruct
     * @covers Bankiru\Stash\TaggedItem::setTags
     * @covers Bankiru\Stash\TaggedItem::getTags
     */
    public function testSetGetTags()
    {
        $item = new TaggedItem();

        $tags = [uniqid('test-tag-', true)];

        static::assertInstanceOf('Bankiru\Stash\TaggedItem', $item->setTags($tags), 'TaggedItem->setTags MUST return self');
        static::assertEquals($tags, $item->getTags(), 'Tags array are not equal');
    }

    /**
     * @depends testSetGetTags
     * @covers Bankiru\Stash\TaggedItem::save
     */
    public function testSaveWithoutTags()
    {
        $key   = uniqid('test/key/', true);
        $value = uniqid('test-value-', true);

        static::assertTrue($this->pool->getItem($key)->set($value)->save(), 'Driver class able to store data');

        /** @var TaggedItem $item */
        $item = $this->pool->getItem($key);
        static::assertTrue($item->isHit());
        static::assertEquals($value, $item->get());
    }

    /**
     * @depends testSetGetTags
     * @depends testSaveWithoutTags
     * @depends testGetVersionedTags
     * @covers Bankiru\Stash\TaggedItem::save
     */
    public function testSaveWithTags()
    {
        $key   = uniqid('test/key/', true);
        $value = uniqid('test-value-', true);
        $tags  = [uniqid('test-tag-', true), uniqid('test-tag-', true)];

        /** @var TaggedItem $item */
        $item = $this->pool->getItem($key);
        $item->set($value)->setTags($tags);

        static::assertTrue($item->save(), 'Driver class able to store data');

        /** @var TaggedItem $item */
        $item = $this->pool->getItem($key);
        static::assertTrue($item->isHit());
        static::assertEquals($value, $item->get());
        static::assertEquals($tags, $item->getTags());
    }

    /**
     * @depends testSaveWithTags
     * @covers Bankiru\Stash\TaggedItem::clearByTags
     */
    public function testClearByTags()
    {
        $key   = uniqid('test/key/', true);
        $value = uniqid('test-value-', true);
        $tags  = [uniqid('test-tag-', true), uniqid('test-tag-', true)];

        /** @var TaggedItem $item */
        $item = $this->pool->getItem($key);
        $item->set($value)->setTags($tags);

        static::assertTrue($item->save(), 'Driver class able to store data');
        static::assertFalse($item->clearByTags([]));
        static::assertTrue($item->clearByTags([$tags[0]]));
        static::assertTrue($this->pool->getItem($key)->isMiss());
    }

    /**
     * @depends testSaveWithTags
     * @covers Bankiru\Stash\TaggedItem::get
     * @covers Bankiru\Stash\TaggedItem::validateRecord
     */
    public function testGetWithInvalidTagVersion()
    {
        $key   = uniqid('test/key/', true);
        $value = uniqid('test-value-', true);
        $tags  = [uniqid('test-tag-', true), uniqid('test-tag-', true)];

        /** @var TaggedItem $item */
        $item = $this->pool->getItem($key);
        $item->set($value)->setTags($tags)->save();

        $tagToInvalidate = 'Bankiru\Stash\TaggedItem/' . TaggedItem::VERSION . '/' . $tags[0];
        $tagItem         = $this->pool->getItem($tagToInvalidate);
        static::assertTrue($tagItem->isHit());
        $tagItem->set('INVALIDVERSION')->save();

        $item = $this->pool->getItem($key);
        static::assertTrue($item->isMiss());
    }
}
