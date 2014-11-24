<?php

/**
 * this file contains \QUI\Feed\Handler\AbstractFeed
 */

namespace QUI\Feed\Handler;

use QUI\QDOM;
use QUI\Feed\Interfaces\Feed as FeedInterface;
use QUI\Feed\Interfaces\Channel as ChannelInterface;

/**
 * Class AbstractFeed
 *
 * @package quiqqer/feed
 * @author www.pcsg.de (Henning Leutz)
 */
abstract class AbstractFeed extends QDOM implements FeedInterface
{
    /**
     * Channel list
     * @var array
     */
    protected $_channels = array();

    /**
     * Add a channel to the feed
     * @param ChannelInterface $Channel
     */
    public function addChannel(ChannelInterface $Channel)
    {
        $this->_channels[] = $Channel;
    }

    /**
     * Return the channels
     * @return Array
     */
    public function getChannels()
    {
        return $this->_channels;
    }

    /**
     * Return the XML of the feed
     * @return String
     */
    public function create()
    {
        $XML = $this->getXML();

        $Dom = new \DOMDocument( '1.0', 'UTF-8' );
        $Dom->preserveWhiteSpace = false;
        $Dom->formatOutput       = true;
        $Dom->loadXML( $XML->asXML() );

        return $Dom->saveXML();
    }
}