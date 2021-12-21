<?php

/**
 * This file contains \QUI\Feed\Handler\GoogleSitemap\Channel
 */

namespace QUI\Feed\Handler\GoogleSitemap;

use QUI\Feed\Handler\AbstractChannel;

/**
 * Class Channel - Google Sitemap XML
 *
 * @package quiqqer/feed
 * @author  www.pcsg.de (Henning Leutz)
 */
class Channel extends AbstractChannel
{
    /**
     * @param array $params
     *
     * @return \QUI\Feed\Interfaces\FeedItemInterface
     */
    public function createItem(array $params = array())
    {
        $Item = new Item($params);
        $this->addItem($Item);

        return $Item;
    }
}
