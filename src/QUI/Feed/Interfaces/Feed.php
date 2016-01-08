<?php

/**
 * This file contains QUI\Feed\Interfaces\Feed
 */

namespace QUI\Feed\Interfaces;

use QUI\Feed\Utils\SimpleXML;

/**
 * Interface Feed
 *
 * @package quiqqer/feed
 * @author  www.pcsg.de (Henning Leutz)
 */
interface Feed
{
    /**
     * Add a feed channel
     *
     * @param \QUI\Feed\Interfaces\Channel $Channel
     */
    public function addChannel(Channel $Channel);

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
     * Return the Feed
     *
     * @return string
     */
    public function create();

    /**
     * Return the DOMDocument of the Feed
     *
     * @return SimpleXML
     */
    public function getXML();
}