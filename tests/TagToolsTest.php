<?php
namespace Bankiru\Stash\tests;

use Bankiru\Stash\Tests\Stubs\TagToolsStub;
use Stash\Driver\Ephemeral;
use Stash\Pool;

/**
 * Class TagToolsTest.
 */
class TagToolsTest extends \PHPUnit_Framework_TestCase
{
    /** @var TagToolsStub */
    private $tagTools;
    private $poolClass = '\Stash\Pool';

    const MANGLED_TAG_REGEXP = '@^Bankiru\\\\Stash\\\\Tests\\\\Stubs\\\\TagToolsStub_\d+\.\d+_##TAGNAME##$@';

    protected function setUp()
    {
        parent::setUp();

        /** @var Pool $poolStub */
        $poolStub = new $this->poolClass();
        $poolStub->setDriver(new Ephemeral([]));

        $this->tagTools = new TagToolsStub();
        $this->tagTools->setPool($poolStub);
    }

    public function testMangleTag()
    {
        $tag    = 'test-tag';
        $mangledTag = $this->tagTools->_mangleTag($tag);
        static::assertInternalType('array', $mangledTag);
        static::assertEquals(['Bankiru\Stash\Tests\Stubs\TagToolsStub', '1.0', $tag], $mangledTag);
    }

    /**
     * @depends testMangleTag
     */
    public function testMangleTags()
    {
        $tags        = ['test-tag'];
        $mangledTags = $this->tagTools->_mangleTags($tags);
        static::assertInternalType('array', $mangledTags);
        static::assertCount(1, $mangledTags);
        static::assertContainsOnly('array', $mangledTags);
        static::assertEquals(['Bankiru\Stash\Tests\Stubs\TagToolsStub', '1.0', $tags[0]], $mangledTags[0]);
    }

    public function testGenerateNewTagVersion()
    {
        $version1 = $this->tagTools->_generateNewTagVersion();
        static::assertRegExp('@^[0-9a-f]{40}$@', $version1);

        $version2 = $this->tagTools->_generateNewTagVersion();
        static::assertRegExp('@^[0-9a-f]{40}$@', $version2);

        static::assertNotEquals($version1, $version2);
    }

    public function testGetTags()
    {
        $tags = ['test-tag-1', 'test-tag-2'];

        $generator = $this->tagTools->_getTags($tags);
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
     * @depends testGenerateNewTagVersion
     * @depends testGetTags
     */
    public function testGetVersionedTags()
    {
        $tags = ['test-tag-1', 'test-tag-2'];

        $generator = $this->tagTools->_getVersionedTags($tags);
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
}
