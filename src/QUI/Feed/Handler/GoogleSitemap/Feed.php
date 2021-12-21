<?php

/**
 * This file contains \QUI\Feed\Handler\GoogleSitemapFeed
 */

namespace QUI\Feed\Handler\GoogleSitemap;

use QUI\Feed\Feed as FeedInstance;
use QUI\Feed\Handler\AbstractFeedType;
use QUI\Feed\Handler\AbstractItem;
use QUI\Feed\Handler\AbstractSiteFeedType;
use QUI\Feed\Utils\SimpleXML;
use QUI\System\Log;

/**
 * Class Feed
 *
 * @package quiqqer/feed
 * @author  www.pcsg.de (Henning Leutz)
 */
class Feed extends AbstractSiteFeedType
{
    /**
     * @var int
     */
    protected $pageSize = 0;

    /**
     * @var int
     */
    protected $page = 0;

    /**
     * Create a channel
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
     * Return the Feed as an XML string
     *
     * @param FeedInstance $Feed - The Feed that shall be created
     * @param int|null $page (optional) - Get a specific page of the feed (only required if feed is paginated)
     * @return string - Feed as XML string
     */
    public function create(FeedInstance $Feed, ?int $page = null): string
    {
        $this->setPage($page);
        $this->setPageSize($Feed->getAttribute('pageSize'));

        return parent::create($Feed, $page);
    }

    /**
     * Return XML of the feed
     *
     * @return \SimpleXMLElement
     */
    public function getXML()
    {
        $Items = [];
        /** @var Channel[] $Channels */
        $Channels = $this->getChannels();

        /** @var Channel $Channel */
        foreach ($Channels as $Channel) {
            $ChannelItems = $Channel->getItems();

            $Items = array_merge($ChannelItems, $Items);
        }

        // Filter items
        $Items = \array_filter($Items, function ($Item) {
            /** @var AbstractItem $Item */
            $seoDirective = $Item->getAttribute('seoDirective');

            if (!empty($seoDirective) && \mb_strpos($seoDirective, 'noindex') !== false) {
                return false;
            }

            return true;
        });

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
            return $this->createSitemapXML([]);
        }

        // Pagination - page
        $startIndex = ($this->page - 1) * $this->pageSize;
        $pageItems  = array_slice($Items, $startIndex, $this->pageSize);

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
            $date    = $Item->getAttribute('e_date');

            $ItemXml->addChild('loc', $Item->getAttribute('link'));

            $ItemXml->addChild(
                'lastmod',
                date(\DateTime::ATOM, (int)$date)
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
            $sitemapURL = $baseURL."-".$i.".xml";
            $SitemapXML = $XML->addChild("sitemap");
            $SitemapXML->addChild("loc", $sitemapURL);
        }

        return $XML;
    }

    /**
     * @param $pageSize
     */
    protected function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
    }

    /**
     * @param $page
     */
    protected function setPage($page)
    {
        $this->page = $page;
    }
}
