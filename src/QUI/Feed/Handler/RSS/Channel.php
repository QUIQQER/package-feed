<?php

/**
 * This file contains \QUI\Feed\Handler\RSS\Channel
 */

namespace QUI\Feed\Handler\RSS;

use QUI\Feed\Handler\AbstractChannel;

/**
 * Class Channel - RSS Feed 2.0
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
    public function createItem(array $params = [])
    {
        $Item = new Item($params);
        $this->addItem($Item);

        return $Item;
    }
}
