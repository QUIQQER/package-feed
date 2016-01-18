<?php

/**
 * This file contains \QUI\Feed\Handler\Atom\Channel
 */

namespace QUI\Feed\Handler\Atom;

use QUI\Feed\Handler\AbstractChannel;

/**
 * Class Channel - Atom Feed
 *
 * @package quiqqer/feed
 * @author  www.pcsg.de (Henning Leutz)
 */
class Channel extends AbstractChannel
{
    /**
     * @param array $params
     *
     * @return \QUI\Feed\Interfaces\FeedItem
     */
    public function createItem(array $params = array())
    {
        $Item = new Item($params);
        $this->addItem($Item);

        return $Item;
    }
}
