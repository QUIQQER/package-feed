<?php

/**
 * This file contains \QUI\Feed\EventHandler
 */

namespace QUI\Feed;

use QUI;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Events -> System Events
 *
 * @package QUI\Feed
 * @author  www.pcsg.de (Henning Leutz)
 */
class EventHandler
{
    /**
     * event : on request
     *
     * @param \QUI\Rewrite $Rewrite
     * @param String $url
     *
     * @return void
     */
    public static function onRequest($Rewrite, $url)
    {
        if (stripos($url, 'feed=') === false) {
            return;
        }

        if (strpos($url, '.xml') === false) {
            return;
        }

        $params = str_replace('.xml', '', $url);
        $params = explode('=', $params);

        if (!isset($params[1])) {
            return;
        }

        // The identifier of the name, can be either just the feed id or the feedid with the pagenomber, for paginated feeds
        $feedIdentifier = $params[1];
        $feedDetails    = explode("-", $feedIdentifier);
        $feedId         = (int)$feedDetails[0];
        $pageNo         = 0;

        if (isset($feedDetails[1])) {
            $pageNo = (int)$feedDetails[1];
        }

        try {
            $Manager = new Manager();
            $Feed    = $Manager->getFeed($feedId);
        } catch (\Exception $Exception) {
            $Response = new Response("Feed not found", 404);
            $Response->send();
            exit;
        }

        if ($pageNo > $Feed->getPageCount()) {
            $Response = new Response("Page not found", 404);
            $Response->send();
            exit;
        }


        $cacheName = 'quiqqer/feed/'.$feedId."/".$pageNo;

        try {
            header('Content-Type: application/rss+xml; charset=UTF-8');
            echo QUI\Cache\Manager::get($cacheName);
            exit;
        } catch (QUI\Exception $Exception) {
        }

        try {
            header('Content-Type: application/rss+xml; charset=UTF-8');
            $output = $Feed->output($pageNo);

            QUI\Cache\Manager::set($cacheName, $output);

            echo $output;
            exit;
        } catch (\Exception $Exception) {
        }
    }


    /**
     * event : site change it
     *
     * @param \QUI\Projects\Site $Site
     */
    public static function onSiteChange($Site)
    {
        // get feeds by project
        $Project = $Site->getProject();
        $Manager = new Manager();

        $projectName = $Project->getName();
        $projectLang = $Project->getLang();

        $feedList = $Manager->getList();

        foreach ($feedList as $feed) {
            if ($projectName != $feed['project']) {
                continue;
            }

            if ($projectLang != $feed['lang']) {
                continue;
            }

            // clear cache
            QUI\Cache\Manager::clear('quiqqer/feed/'.$feed['id']);
        }
    }

    /**
     * @param QUI\Template $Template
     */
    public static function onTemplateGetHeader($Template)
    {
        $Manager  = new Manager();
        $feedrows = $Manager->getList();

        foreach ($feedrows as $databaseRow) {
            $feedID = $databaseRow['id'];

            try {
                $Feed = new Feed($feedID);
            } catch (\Exception $Exception) {
                QUI\System\Log::addWarning("Attempt to add non existing feed '".$feedID."' to header");
                continue;
            }

            if ($Feed->getAttribute("publish") == false) {
                continue;
            }

            $FeedProject = QUI::getProject(
                $Feed->getAttribute('project'),
                $Feed->getAttribute('lang')
            );

            // Only display feeds for the current project and language
            $curProject = QUI::getRewrite()->getProject();
            if ($curProject->getName() != $FeedProject->getName()) {
                continue;
            }

            if ($curProject->getLang() != $FeedProject->getLang()) {
                continue;
            }


            // Check if the feed should be included on this page
            $publishSitesString = $Feed->getAttribute("publish_sites");
            $feedPublishSiteIDs = $Feed->parseSiteSelect($publishSitesString);

            if (!empty($publishSitesString) && !in_array(QUI::getRewrite()->getSite()->getId(), $feedPublishSiteIDs)) {
                continue;
            }


            $projectHost = $FeedProject->getVHost(true, true);
            $url         = $projectHost.URL_DIR.'feed='.$Feed->getId().'.xml';

            $mimeType = "";
            $feedType = $Feed->getFeedType();

            // Do not pusblish google sitemaps
            if ($feedType == "googleSitemap") {
                continue;
            }

            if ($feedType == "rss") {
                $mimeType = "application/rss+xml";
            }

            if ($feedType == "atom") {
                $mimeType = "application/atom+xml";
            }


            $rssTag = '<link rel="alternate" type="'.$mimeType.'" href="'.$url.'" />'.PHP_EOL;
            $Template->extendHeader($rssTag);
        }
    }
}
