<?php

/**
 * This file contains \QUI\Feed\Handler\GoogleSitemapFeed
 */

namespace QUI\Feed\Handler\GoogleSitemap;

use QUI\Feed\Handler\AbstractFeed;

/**
 * Class Feed
 *
 * @package quiqqer/feed
 * @author www.pcsg.de (Henning Leutz)
 */
class Feed extends AbstractFeed
{
    /**
     * Creat a channel
     * @return Channel
     */
    public function createChannel()
    {
        $Channel = new Channel();

        $this->addChannel( $Channel );

        return $Channel;
    }

    /**
     * Return the feed
     * @return String
     */
    public function getDom()
    {

    }
}
