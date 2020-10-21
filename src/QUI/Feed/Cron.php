<?php

/**
 * This file contains QUI\Feed\Cron
 */

namespace QUI\Feed;

use QUI;

/**
 * Class Cron
 *
 * @package QUI\Feed
 */
class Cron
{
    /**
     * Generates a cache entry for all feeds
     */
    public static function buildFeeds()
    {
        $Manager = new QUI\Feed\Manager();

        $feedList = $Manager->getList();

        foreach ($feedList as $feedRow) {
            $feedID = $feedRow['id'];

            try {
                $Feed = new QUI\Feed\Feed($feedID);
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
                continue;
            }

            for ($pageNo = 0; $pageNo <= $Feed->getPageCount(); $pageNo++) {
                $output = $Feed->output($pageNo);

                $cacheName = 'quiqqer/feed/'.$Feed->getId()."/".$pageNo;

                try {
                    QUI\Cache\Manager::set($cacheName, $output);
                } catch (\Exception $Exception) {
                    QUI\System\Log::writeException($Exception);
                }
            }
        }
    }
}
