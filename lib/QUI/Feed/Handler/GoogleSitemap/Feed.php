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
     * Return XML of the feed
     * @return \SimpleXMLElement
     */
    public function getXML()
    {
        $XML = new SimpleXML(
            '<?xml version="1.0" encoding="UTF-8"?>
            <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" />'
        );




        return $XML;
    }
}
