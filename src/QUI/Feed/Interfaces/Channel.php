<?php

/**
 * This file contains QUI\Feed\Interfaces\Channel
 */

namespace QUI\Feed\Interfaces;

/**
 * Interface Feed
 *
 * @package quiqqer/feed
 * @author  www.pcsg.de (Henning Leutz)
 */
interface Channel
{
    /**
     * Add an feed item
     *
     * @param \QUI\Feed\Interfaces\FeedItem $Item
     */
    public function addItem(FeedItem $Item);

    /**
     * Create a item and add it to the channel
     *
     * @param array $params
     *
     * @return FeedItem
     */
    public function createItem(array $params = array());

    /**
     * Return the feed items
     *
     * @return array
     */
    public function getItems();

    /**
     * Set the title of the channel
     *
     * @param string $title
     */
    public function setTitle($title);

    /**
     * Set the description of the channel
     *
     * @param string $description
     */
    public function setDescription($description);

    /**
     * Set the unix timestamp
     *
     * @param integer $timestamp - Unix timestamp
     */
    public function setDate($timestamp);

    /**
     * Set the language of the channel
     *
     * @param string $language
     */
    public function setLanguage($language);

    /**
     * Host
     */

    /**
     * Set the main host
     *
     * @param string $host - http://my.host.com
     */
    public function setHost($host);

    /**
     * Return the main host
     *
     * @return string
     */
    public function getHost();
}
