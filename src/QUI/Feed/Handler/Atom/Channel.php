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
     * @return \QUI\Feed\Interfaces\FeedItemInterface
     */
    public function createItem(array $params = [])
    {
        $Item = new Item($params);
        $this->addItem($Item);

        return $Item;
    }
}
