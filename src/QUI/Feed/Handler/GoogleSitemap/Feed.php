<?php

/**
 * This file contains \QUI\Feed\Handler\GoogleSitemapFeed
 */

namespace QUI\Feed\Handler\GoogleSitemap;

use QUI\Feed\Handler\AbstractFeed;
use QUI\Feed\Utils\SimpleXML;

/**
 * Class Feed
 *
 * @package quiqqer/feed
 * @author  www.pcsg.de (Henning Leutz)
 */
class Feed extends AbstractFeed
{
    protected $pageSize = 0;
    protected $page = 0;


    /**
     * Creat a channel
     *
     * @return Channel
     */
    public function createChannel()
    {
        $Channel = new Channel();

        $this->addChannel($Channel);

        return $Channel;
    }

    /**
     * Return XML of the feed
     *
     * @return \SimpleXMLElement
     */
    public function getXML()
    {

        $Items = array();
        /** @var Channel[] $Channels */
        $Channels = $this->getChannels();

        /** @var Channel $Channel */
        foreach ($Channels as $Channel) {
            $ChannelItems = $Channel->getItems();

            $Items = array_merge($ChannelItems, $Items);
        }
        
        
        if ($this->pageSize == 0) {
            return $this->createSitemapXML($Items);
        }


        // Pagination - index

        // The link attribute ends on .rss - we need to strip that
        $baseURL = $Channels[0]->getAttribute("link");
        if (substr($baseURL, -4) == ".rss") {
            $baseURL = substr($baseURL, 0, -4);
        }

        // Caclcualte the pages
        $itemCount = count($Items);
        $pageCount = ceil($itemCount / $this->pageSize);

        // Return the sitemap index for page 0
        if ($this->page == 0) {
            return $this->createSitemapIndexXML($pageCount, $baseURL);
        }

        # Check if the site can exist
        if ($this->page < 0 || $this->page > $pageCount) {
            return $this->createSitemapXML(array());
        }

        // Pagination - page
        $startIndex = ($this->page - 1) * $this->pageSize;
       
        $pageItems = array_slice($Items, $startIndex, $this->pageSize);

        return $this->createSitemapXML($pageItems);

    }


    /**
     * @param Item[] $items
     *
     * @return SimpleXML
     */
    protected function createSitemapXML($items)
    {
        $XML = new SimpleXML(
            '<?xml version="1.0" encoding="UTF-8"?>
            <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" />'
        );

        foreach ($items as $Item) {
            /* @var $Item Item */
            $ItemXml = $XML->addChild('url');

            $ItemXml->addChild('loc', $Item->getAttribute('link'));

            $ItemXml->addChild(
                'lastmod',
                date(\DateTime::ATOM, (int)$Item->getAttribute('date'))
            );
        }

        return $XML;
    }

    /**
     * @param $pages
     * @param $baseURL
     *
     * @return SimpleXML
     */
    protected function createSitemapIndexXML($pages, $baseURL)
    {
        $XML = new SimpleXML(
            '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" />'
        );
        
        for ($i = 1; $i <= $pages; $i++) {
            $sitemapURL = $baseURL . "-" . $i . ".xml";
            $SitemapXML = $XML->addChild("sitemap");
            $SitemapXML->addChild("loc", $sitemapURL);
        }

        return $XML;
    }

    /**
     * @param $pageSize
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
    }

    /**
     * @param $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }
}
