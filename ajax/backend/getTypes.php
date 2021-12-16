<?php

/**
 * Get all feed types installed in this system
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_feed_ajax_backend_getTypes',
    function () {
        $FeedManager = new QUI\Feed\Manager();


    },
    [],
    'Permission::checkAdminUser'
);
