<?php

/**
 * this file contains \QUI\Feed\Handler\AbstractFeed
 */

namespace QUI\Feed\Handler;

use QUI\Feed\Feed;
use QUI\QDOM;
use QUI\Feed\Interfaces\FeedTypeInterface as FeedInterface;
use QUI\Feed\Interfaces\Channel as ChannelInterface;

/**
 * Class AbstractFeed
 *
 * @package quiqqer/feed
 * @author  www.pcsg.de (Henning Leutz)
 */
abstract class AbstractFeedType extends QDOM implements FeedInterface
{
    /**
     * @var Feed
     */
    protected Feed $Feed;

    /**
     * @param Feed $Feed
     */
    public function __construct(Feed $Feed)
    {
        $this->Feed = $Feed;
    }

    /**
     * Channel list
     *
     * @var array
     */
    protected $channels = [];

    /**
     * Add a channel to the feed
     *
     * @param ChannelInterface $Channel
     */
    public function addChannel(ChannelInterface $Channel)
    {
        $this->channels[] = $Channel;
    }

    /**
     * Return the channels
     *
     * @return array
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * Return the XML of the feed
     *
     * @return string
     */
    public function create()
    {
        $XML = $this->getXML();

        $Dom                     = new \DOMDocument('1.0', 'UTF-8');
        $Dom->preserveWhiteSpace = false;
        $Dom->formatOutput       = true;
        $Dom->loadXML($XML->asXML());

        return $Dom->saveXML();
    }
}
