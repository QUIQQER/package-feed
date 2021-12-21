<?php

namespace QUI\Feed\Interfaces;

use QUI;
use QUI\Feed\Feed;
use QUI\Feed\Utils\SimpleXML;

/**
 * Interface Feed
 *
 * @package quiqqer/feed
 * @author  www.pcsg.de (Henning Leutz)
 * @author  www.pcsg.de (Patrick Müller)
 */
interface FeedTypeInterface extends QUI\QDOMInterface
{
    /**
     * @param array $attributes - Feed type attributes
     */
    public function __construct(array $attributes = []);

    /**
     * Add a feed channel
     *
     * @param \QUI\Feed\Interfaces\ChannelInterface $Channel
     */
    public function addChannel(ChannelInterface $Channel);

    /**
     * Create a new channel and add it to the feed
     */
    public function createChannel();

    /**
     * Return the channel list
     *
     * @return array
     */
    public function getChannels();

    /**
     * Return the Feed as an XML string
     *
     * @param Feed $Feed - The Feed that shall be created
     * @param int|null $page (optional) - Get a specific page of the feed (only required if feed is paginated)
     * @return string - Feed as XML string
     */
    public function create(Feed $Feed, ?int $page = null): string;

    /**
     * Returns the number of pages of this feed.
     *
     * @param Feed $Feed
     * @return int - Returns the number of pages or 0 if nor pages are used
     */
    public function getPageCount(Feed $Feed): int;

    /**
     * Check if $Feed shall be published on $Site
     *
     * @param Feed $Feed
     * @param QUI\Projects\Site $Site
     * @return bool
     */
    public function publishOnSite(Feed $Feed, QUI\Projects\Site $Site): bool;
}
