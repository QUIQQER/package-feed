<?php

/**
 * This file contains package_quiqqer_feed_ajax_getList
 */

/**
 * Returns the feed list
 *
 * @author www.pcsg.de (Henning Leutz)
 *
 * @param string $gridParams - grid params
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_feed_ajax_getList',
    function ($gridParams) {
        $FeedManager = new QUI\Feed\Manager();
        $gridParams  = json_decode($gridParams, true);

        $Grid   = new QUI\Utils\Grid();
        $result = $FeedManager->getList($gridParams);

        return $Grid->parseResult($result, $FeedManager->count());
    },
    array('gridParams'),
    'Permission::checkAdminUser'
);
