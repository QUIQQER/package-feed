<?php

/**
 * This file contains package_quiqqer_feed_ajax_getFeed
 */

/**
 * Returns the feed list
 *
 * @author www.pcsg.de (Henning Leutz)
 *
 * @param integer $feedId - ID of the Feed
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_feed_ajax_getFeed',
    function ($feedId) {
        $FeedManager = new QUI\Feed\Manager();

        return $FeedManager->getFeed($feedId)->getAttributes();
    },
    array('feedId'),
    'Permission::checkAdminUser'
);
