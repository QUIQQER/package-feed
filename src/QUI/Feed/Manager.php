<?php

/**
 * This file contains \QUI\Feed\Manager
 */

namespace QUI\Feed;

use QUI;
use QUI\Utils\Grid;

/**
 * Class Feed Manager
 *
 * @package quiqqer/feed
 * @author  www.pcsg.de (Henning Leutz
 */
class Manager
{
    /**
     * Feed Table
     */
    const TABLE = 'feeds';

    /**
     * Add a new feed
     *
     * @param array $params - Feed attributes
     *
     * @return Feed
     */
    public function addFeed($params)
    {
        QUI::getDataBase()->insert(
            QUI::getDBTableName(self::TABLE),
            ['feedtype' => 'rss']
        );

        $id   = QUI::getDataBase()->getPDO()->lastInsertId();
        $Feed = new Feed($id);

        $Feed->setAttributes($params);
        $Feed->save();

        return $Feed;
    }

    /**
     * Return the Feed
     *
     * @param integer $feedId - ID of the Feed
     *
     * @return Feed
     * @throws QUI\Exception
     */
    public function getFeed($feedId)
    {
        return new Feed($feedId);
    }

    /**
     * Delete a feed
     *
     * @param integer $feedId - ID of the Feed
     */
    public function deleteFeed($feedId)
    {

        try {
            $feedId = (int)$feedId;

            $this->getFeed($feedId);

            QUI::getDataBase()->delete(
                QUI::getDBTableName(Manager::TABLE),
                [
                    'id' => $feedId
                ]
            );

        } catch (QUI\Exception $Exception) {
            // feed not exist
        }
    }

    /**
     * Return the feed entries
     *
     * @param array $params
     *
     * @return array
     */
    public function getList($params = [])
    {
        if (empty($params)) {
            return QUI::getDataBase()->fetch([
                'from' => QUI::getDBTableName(self::TABLE)
            ]);
        }

        $Grid = new Grid();

        $params = array_merge($Grid->parseDBParams($params), [
            'from' => QUI::getDBTableName(self::TABLE)
        ]);

        return QUI::getDataBase()->fetch($params);
    }

    /**
     * Get list of all available feed types
     *
     * @return void
     */
    public function getTypes()
    {

    }

    /**
     * Return the number of the feeds
     *
     * @return integer
     */
    public function count()
    {
        $result = QUI::getDataBase()->fetch([
            'count' => [
                'select' => 'id',
                'as'     => 'count'
            ],
            'from'  => QUI::getDBTableName(self::TABLE)
        ]);

        return (int)$result[0]['count'];
    }
}
