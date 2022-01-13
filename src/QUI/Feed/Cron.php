<?php

namespace QUI\Feed;

use QUI;

/**
 * Class Cron
 *
 * Cronjob manager for quiqqer/feed.
 */
class Cron
{
    /**
     * Generates a cache entry for all feeds
     *
     * @return void
     */
    public static function buildFeeds(): void
    {
        $Manager  = new QUI\Feed\Manager();
        $feedList = $Manager->getList();

        foreach ($feedList as $feedRow) {
            try {
                $Feed = new QUI\Feed\Feed($feedRow['id']);
                $Manager->buildFeed($Feed);
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }
    }
}
