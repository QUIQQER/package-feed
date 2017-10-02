<?php

namespace QUI\Feed;

class Cron
{
    /**
     * Generates a cache entry for all feeds
     */
    public static function buildFeeds()
    {
        $Manager = new \QUI\Feed\Manager();

        $feedList = $Manager->getList();

        foreach ($feedList as $feedRow) {
            $feedID = $feedRow['id'];

            $Feed = new \QUI\Feed\Feed($feedID);

            for ($pageNo = 0; $pageNo <= $Feed->getPageCount(); $pageNo++) {
                $output = $Feed->output($pageNo);

                $cacheName = 'quiqqer/feed/' . $Feed->getId() . "-" . $pageNo;
                \QUI\Cache\Manager::set($cacheName, $output);
            }
        }
    }
}