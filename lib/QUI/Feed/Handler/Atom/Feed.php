<?php

/**
 * This file contains \QUI\Feed\Handler\Atom\Feed
 */

namespace QUI\Feed\Handler\Atom;

use QUI\Feed\Handler\AbstractFeed;
use \QUI\Feed\Utils\SimpleXML;

/**
 * Class Feed
 *
 * @package quiqqer/feed
 * @author  www.pcsg.de (Henning Leutz)
 */
class Feed extends AbstractFeed
{
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
     * Return the XML of the feed
     *
     * @return SimpleXML
     */
    public function getXML()
    {
        $XML = new SimpleXML(
            '<?xml version="1.0" encoding="UTF-8" ?>
             <rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" />'
        );

        $channels = $this->getChannels();

        foreach ($channels as $Channel) {
            /* @var $Channel Channel */
            $ChannelXml = $XML->addChild('channel');

            $host = $Channel->getHost();
            $date = date(\DateTime::RFC2822,
                (int)$Channel->getAttribute('timestamp'));

            $Atomlink = $ChannelXml->addChild('link', '',
                'http://www.w3.org/2005/Atom');
            $Atomlink->addAttribute('href', $Channel->getAttribute('link'));
            $Atomlink->addAttribute('rel', "self");
            $Atomlink->addAttribute('type', "application/rss+xml");

            $ChannelXml->addChild('link', $Channel->getAttribute('link'));
            $ChannelXml->addChild('pubDate', $date);
            $ChannelXml->addChild('language',
                $Channel->getAttribute('language'));
            $ChannelXml->addChild('generator',
                'quiqqer.com (http://www.quiqqer.com)');
            $ChannelXml->addChild('description')
                       ->addCData($Channel->getAttribute('description'));
            $ChannelXml->addChild('title')
                       ->addCData($Channel->getAttribute('title'));

            if ($Channel->getAttribute('author')) {
                $ChannelXml->addChild('webMaster',
                    $Channel->getAttribute('author'));
            }

            // channel feed items
            $items = $Channel->getItems();

            foreach ($items as $Item) {
                /* @var $Item Item */
                $date = date(\DateTime::RFC2822,
                    (int)$Item->getAttribute('date'));

                $ItemXml = $ChannelXml->addChild('item');
                $ItemXml->addChild('link', $Item->getAttribute('link'));
                $ItemXml->addChild('pubDate', $date);
                $ItemXml->addChild('guid', $Item->getAttribute('permalink'));
                $ItemXml->addChild('title')
                        ->addCData($Item->getAttribute('title'));
                $ItemXml->addChild('description')
                        ->addCData($Item->getAttribute('description'));

                /* @var $Image \QUI\Projects\Media\Image */
                $Image = $Item->getImage();

                if (!$Image) {
                    continue;
                }

                if (!$Image->isActive()) {
                    continue;
                }

                $EnclosureDom = $ItemXml->addChild('enclosure');
                $EnclosureDom->addAttribute('url',
                    $host.trim($Image->getUrl(true), '/'));
                $EnclosureDom->addAttribute('length',
                    $Image->getAttribute('filesize'));
                $EnclosureDom->addAttribute('type',
                    $Image->getAttribute('mime_type'));
            }
        }

        return $XML;
    }
}
