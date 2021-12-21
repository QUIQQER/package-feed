<?php

namespace QUI\Feed;

use QUI;
use Symfony\Component\HttpFoundation\Response;
use QUI\Cache\LongTermCache;
use QUI\Feed\Handler\RSS\Feed as FeedRSS;
use QUI\Feed\Handler\Atom\Feed as FeedAtom;
use QUI\Feed\Handler\GoogleSitemap\Feed as FeedGoogleSitemap;

/**
 * Class Events -> System Events
 *
 * @package QUI\Feed
 * @author  www.pcsg.de (Henning Leutz)
 */
class EventHandler
{
    /**
     * quiqqer/quiqqer: onPackageSetup
     *
     * @param QUI\Package\Package $Package
     * @return void
     */
    public static function onPackageSetup(QUI\Package\Package $Package)
    {
        if ($Package->getName() !== 'quiqqer/feed') {
            return;
        }

        LongTermCache::clear(QUI\Feed\Utils\Utils::getFeedTypeCachePath());

        self::patchV1();
    }

    /**
     * Patch database for migration from quiqqer/feed 1.*
     *
     * @return void
     */
    protected static function patchV1(): void
    {
        $result = QUI::getDataBase()->fetch([
            'from'  => QUI::getDBTableName(Manager::TABLE),
            'where' => [
                'type_id' => null
            ]
        ]);

        $Manager             = new Manager();
        $types               = $Manager->getTypes();
        $feedIdRss           = '1de938991bab7c523b9adbb631de5077588ecd348a68e7d993f619200f5a8bec';
        $feedIdAtom          = 'b71ca88546347228c7a9057939de67a49852df3f5fc90fac389bc19f509f7bc1';
        $feedIdGoogleSitemap = '2db63240d86aee59430c7c17f92039cb954d2e88fde05889ea3a60a6266462cb';

        foreach ($result as $row) {
            $update = [];

            switch ($row['feedtype']) {
                case 'googleSitemap':
                    $update['type_id'] = $feedIdGoogleSitemap;
                    break;

                case 'atom':
                    $update['type_id'] = $feedIdAtom;
                    break;

                case 'rss':
                    $update['type_id'] = $feedIdRss;
                    break;
            }

            $update['feed_settings'] = \json_encode([
                'feedsites'         => !empty($row['feedsites']) ? $row['feedsites'] : '',
                'feedsites_exclude' => !empty($row['feedsites_exclude']) ? $row['feedsites_exclude'] : ''
            ]);


        }
    }

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

            try {
                $FeedType = $Manager->getType($Feed->getTypeId());
            } catch (\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
                continue;
            }

            if (empty($Feed->getAttribute('publish')) || empty($FeedType->getAttribute('publishable'))) {
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
            if (!$Feed->publishOnSite(QUI::getRewrite()->getSite())) {
                continue;
            }

            $projectHost = $FeedProject->getVHost(true, true);
            $url         = $projectHost.URL_DIR.'feed='.$Feed->getId().'.xml';
            $mimeType    = $FeedType->getAttribute('mimeType');

            $rssTag = '<link rel="alternate" type="'.$mimeType.'" href="'.$url.'" />'.PHP_EOL;
            $Template->extendHeader($rssTag);
        }
    }
}
