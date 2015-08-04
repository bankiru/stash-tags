<?php
namespace Bankiru\Stash;

use Stash\Invalidation;
use Stash\Item;

/**
 * Class TaggedItem.
 */
class TaggedItem extends Item
{
    use TagTools;

    /**
     * @param array $tags
     *
     * @return bool
     */
    public function clearByTags(array $tags = [])
    {
        if (!$tags) {
            return false;
        }
        /** @var Item $tagItem */
        foreach ($this->getTags($tags) as $tagItem) {
            $tagItem->clear();
        }

        return true;
    }

    /**
     * @param mixed $data
     * @param null  $ttl
     * @param array $tags
     *
     * @return bool
     */
    public function set($data, $ttl = null, array $tags = [])
    {
        $data = [
            'data' => $data,
            'tags' => iterator_to_array($this->getVersionedTags($tags)),
        ];

        return parent::set($data, $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function get($invalidation = Invalidation::PRECOMPUTE, $arg = null, $arg2 = null)
    {
        $data = parent::get($invalidation, $arg, $arg2);

        if (
            is_array($data)
            && array_key_exists('data', $data)
            && array_key_exists('tags', $data)
        ) {
            $data = $data['data'];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function validateRecord($validation, &$record)
    {
        $expiration = ($_ = &$record['expiration']);
        if (isset($record['data'], $record['data']['return'], $record['data']['return']['data'], $record['data']['return']['tags'])
            && !empty($record['data']['return']['tags'])
        ) {
            $tags = $record['data']['return']['tags'];
            foreach ($this->getTags(array_keys($record['data']['return']['tags'])) as $tag => $tagItem) {
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
}
