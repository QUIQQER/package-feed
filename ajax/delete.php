<?php

/**
 * This file contains package_quiqqer_feed_ajax_getList
 */

/**
 * Returns the feed list
 *
 * @param string $feedIds - json array, array of feed ids
 * @author www.pcsg.de (Henning Leutz)
 */

QUI::$Ajax->registerFunction(
    'package_quiqqer_feed_ajax_delete',
    function ($feedIds) {
        $FeedManager = new QUI\Feed\Manager();
        $feedIds = json_decode($feedIds, true);

        foreach ($feedIds as $feedId) {
            $FeedManager->deleteFeed($feedId);
        }
    },
    ['feedIds'],
    'Permission::checkAdminUser'
);
