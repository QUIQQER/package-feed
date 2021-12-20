<?php

/**
 * This file contains \QUI\Feed\Feed;
 */

namespace QUI\Feed;

use QUI;
use QUI\Feed\Handler\RSS\Feed as RSS;
use QUI\Feed\Handler\Atom\Feed as Atom;
use QUI\Feed\Handler\GoogleSitemap\Feed as GoogleSitemap;
use QUI\Utils\Security\Orthos;

/**
 * Class Feed
 * One Feed, you can edit and save the feed, and get the feed as an XML / RSS Feed
 *
 * @package quiqqer/feed
 * @author  www.pcsg.de (Henning Leutz)
 */
class Feed extends QUI\QDOM
{
    /**
     * ID of the Feed
     *
     * @var integer
     */
    protected $feedId;

    /**
     * ID of the Feed type (hashed classname)
     *
     * @var string
     */
    protected $typeId = '';

    /**
     * Constructor
     *
     * @param integer $feedId
     *
     * @throws QUI\Exception
     */
    public function __construct(int $feedId)
    {
        $this->feedId = $feedId;

        $data = QUI::getDataBase()->fetch([
            'from'  => QUI::getDBTableName(Manager::TABLE),
            'where' => [
                'id' => $this->feedId
            ],
            'limit' => 1
        ]);

        if (!isset($data[0])) {
            throw new QUI\Exception(
                QUI::getLocale()->get('quiqqer/feed', 'exception.feed.not.found')
            );
        }

        $data = $data[0];

        if (!empty($data['feed_settings'])) {
            $feedSettings = \json_decode($data['feed_settings'], true);
            $this->setAttributes($feedSettings);

            unset($data['feed_settings']);
        }

        if (!empty($data['type_id'])) {
            $this->typeId = $data['type_id'];
        }

        $this->setAttributes($data);
    }

    /**
     * Return the Feed-ID
     *
     * @return integer
     */
    public function getId()
    {
        return $this->feedId;
    }

    /**
     * @return string
     */
    public function getTypeId(): string
    {
        return $this->typeId;
    }

    /**
     * Return the feed tyoe
     *
     * @return string (rss|atom|googleSitemap)
     */
    public function getFeedType()
    {
        $feedtype = 'rss';

        switch ($this->getAttribute('feedtype')) {
            case 'atom':
            case 'googleSitemap':
                $feedtype = $this->getAttribute('feedtype');
                break;
        }

        return $feedtype;
    }

    /**
     * Return the feed attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes       = parent::getAttributes();
        $attributes['id'] = $this->feedId;

        return $attributes;
    }

    /**
     * Save the feed
     *
     * @throws QUI\Exception
     */
    public function save()
    {
        $table = QUI::getDBTableName(Manager::TABLE);

        $feedlimit       = (int)$this->getAttribute('feedlimit');
        $feedName        = '';
        $feedDescription = '';

        if ($this->getAttribute('feedName')) {
            $feedName = Orthos::clear($this->getAttribute('feedName'));
        }

        if ($this->getAttribute('feedDescription')) {
            $feedDescription = Orthos::clear($this->getAttribute('feedDescription'));
        }

        $Manager      = new Manager();
        $typeData     = $Manager->getType($this->getAttribute('type_id'));
        $feedSettings = [];

        foreach ($typeData['attributes'] as $attribute) {
            $feedSettings[$attribute] = $this->getAttribute($attribute);
        }

        QUI::getDataBase()->update($table, [
            'project'         => $this->getAttribute('project'),
            'lang'            => $this->getAttribute('lang'),
            'feedtype'        => $this->getFeedType(),
//            'feedsites'         => $this->getAttribute('feedsites'),
//            'feedsites_exclude' => $this->getAttribute('feedsites_exclude'),
            'feedlimit'       => $feedlimit ?: 0,
            'feedName'        => $feedName,
            'feedDescription' => $feedDescription,
            'pageSize'        => $this->getAttribute("pageSize"),
            'publish'         => $this->getAttribute("publish") ? 1 : 0,
            'publish_sites'   => $this->getAttribute("publish_sites"),
            'feedImage'       => $this->getAttribute("feedImage"),
            'feed_settings'   => \json_encode($feedSettings)
        ], [
            'id' => $this->getId()
        ]);

        // clear cache
        QUI\Cache\Manager::clear('quiqqer/feed/'.$this->getId());
    }

    /**
     * Output the feed as XML
     *
     * @param $page - (optional) The pagenumber, if supported
     *
     * @return string
     *
     * @throws QUI\Exception
     */
    public function output($page = 0)
    {
        $feedType = $this->getFeedType();

        $Project = QUI::getProject(
            $this->getAttribute('project'),
            $this->getAttribute('lang')
        );

        $projectHost = $Project->getVHost(true, true);
        $feedUrl     = $projectHost.URL_DIR.'feed='.$this->getId().'.xml';

        switch ($feedType) {
            case 'atom':
                $Feed = new Atom();
                break;

            case 'googleSitemap':
                $Feed = new GoogleSitemap();
                $Feed->setPageSize($this->getAttribute("pageSize"));
                $Feed->setPage($page);
                break;

            default:
                $Feed = new RSS();
                break;
        }

        $Channel = $Feed->createChannel();

        $Channel->setLanguage($Project->getLang());
        $Channel->setHost($projectHost.URL_DIR);

        $Channel->setAttribute('link', $feedUrl);
        $Channel->setAttribute(
            'description',
            $this->getAttribute('feedDescription')
        );
        $Channel->setAttribute('title', $this->getAttribute('feedName'));
        $Channel->setDate(\time());

        $ids = $this->getSiteIDs();

        // create feed
        foreach ($ids as $id) {
            try {
                $Site = $Project->get($id);
                $date = $Site->getAttribute('release_from');

                if ($date == '0000-00-00 00:00:00') {
                    $date = $Site->getAttribute('c_date');
                }

                // Workaround bug  $Site->getCanonical() come with protocol
                $link      = $Site->getUrlRewritten();
                $permalink = $Site->getCanonical();

                if (\strpos($link, 'https:') === false && \strpos($link, 'http:') === false) {
                    $link = $projectHost.$Site->getUrlRewritten();
                }

                if (\strpos($permalink, 'https:') === false && \strpos($permalink, 'http:') === false) {
                    $permalink = $projectHost.$Site->getCanonical();
                }

                /** @var QUI\Feed\Handler\AbstractItem $Item */
                $Item = $Channel->createItem([
                    'title'        => $Site->getAttribute('title'),
                    'description'  => $Site->getAttribute('short'),
                    'language'     => $Project->getLang(),
                    'date'         => \strtotime($date),
                    'link'         => $link,
                    'permalink'    => $permalink,
                    'seoDirective' => $Site->getAttribute('quiqqer.meta.site.robots')
                ]);

                try {
                    //Check if the create user should be picked
                    if (QUI::getPackage("quiqqer/feed")->getConfig()->get("common", "user") != "c_user") {
                        throw new QUI\Exception("Invalid user field choice!");
                    }

                    $User = QUI::getUsers()->get($Site->getAttribute("c_user"));
                    $Item->setAttribute("author", $User->getName());
                } catch (\Exception $Exception) {
                    $Item->setAttribute(
                        "author",
                        QUI::getPackage("quiqqer/feed")->getConfig()->get("common", "author")
                    );
                }

                // Image
                $image = $Site->getAttribute('image_site');
                if (!$image) {
                    continue;
                }

                $Image = QUI\Projects\Media\Utils::getImageByUrl($image);
                $Item->setImage($Image);
            } catch (QUI\Exception $Exception) {
            }
        }

        return $Feed->create();
    }

    /**
     * Gets the site ids which should be used for the feed
     *
     * @return array
     * @throws QUI\Exception
     */
    public function getSiteIDs()
    {
        $feedSites        = $this->getAttribute('feedsites');
        $feedSitesExclude = $this->getAttribute('feedsites_exclude');

        if (empty($feedSites)) {
            $feedSites = [];
        } else {
            $feedSites = \explode(';', $feedSites);
            $feedSites = \array_filter($feedSites, function ($siteId) {
                return !empty($siteId);
            });
        }

        if (empty($feedSitesExclude)) {
            $feedSitesExclude = [];
        } else {
            $feedSitesExclude = \explode(';', $feedSitesExclude);
            $feedSitesExclude = \array_filter($feedSitesExclude, function ($siteId) {
                return !empty($siteId);
            });
        }

        // Some site types are always excluded!
        $feedSitesExclude[] = 'quiqqer/sitetypes:types/forwarding';

        $feedLimit = (int)$this->getAttribute('feedlimit');

        if (!$feedLimit && $feedLimit !== 0) {
            $feedLimit = 10;
        }

        $Project = QUI::getProject(
            $this->getAttribute('project'),
            $this->getAttribute('lang')
        );

        // All sites, if no sites were selected.
        if (empty($feedSites)) {
            $queryParams = [
                'order' => 'release_from DESC, c_date DESC'
            ];

            if ($feedLimit > 0) {
                $queryParams['limit'] = $feedLimit;
            }

            $ids = $Project->getSitesIds($queryParams);

            $siteIds = \array_map(function ($entry) {
                return (int)$entry['id'];
            }, $ids);
        } else {
            $siteIds = $this->getSiteIdsBySiteIdControlValues($feedSites);
        }

        if (empty($feedSitesExclude)) {
            return $siteIds;
        }

        $siteIdsExclude = $this->getSiteIdsBySiteIdControlValues($feedSitesExclude);

        return \array_diff($siteIds, $siteIdsExclude);
    }

    /**
     * Get all Site IDs based on the values of the controls/projects/project/site/Select control
     *
     * @param array $values
     * @return int[]
     * @throws QUI\Exception
     */
    protected function getSiteIdsBySiteIdControlValues(array $values)
    {
        $Project = QUI::getProject(
            $this->getAttribute('project'),
            $this->getAttribute('lang')
        );

        $PDO      = QUI::getPDO();
        $table    = $Project->getAttribute('db_table');
        $idCount  = 0;
        $strCount = 0;

        $whereParts    = [];
        $wherePrepared = [];
        $childPageIDs  = [];

        $feedLimit = (int)$this->getAttribute('feedlimit');

        if (!$feedLimit && $feedLimit !== 0) {
            $feedLimit = 10;
        }

        foreach ($values as $needle) {
            //
            if (\is_numeric($needle)) {
                $_id = ':id'.$idCount;

                $whereParts[] = " id = {$_id} ";

                $wherePrepared[] = [
                    'type'  => \PDO::PARAM_INT,
                    'value' => $needle,
                    'name'  => $_id
                ];

                $idCount++;
                continue;
            }

            // Search for children of this site

            if (\preg_match("~p[0-9]+~i", $needle)) {
                $parentSiteID = (int)\substr($needle, 1);
                $childPageIDs = \array_merge($childPageIDs, $Project->get($parentSiteID)->getChildrenIdsRecursive());
                continue;
            }

            // Search for type
            $_id = ':str'.$strCount;

            $whereParts[]    = " type LIKE {$_id} ";
            $wherePrepared[] = [
                'type'  => \PDO::PARAM_STR,
                'value' => $needle,
                'name'  => $_id
            ];

            $strCount++;
        }

        // Create the part of the query for the site ids of child sites.
        // `id` IN ( id1, id2, id3, id4 )
        if (!empty($childPageIDs)) {
            $childPageIDs = \array_unique($childPageIDs);

            $idString = "";

            for ($i = 0; $i < \count($childPageIDs); $i++) {
                $idString .= ":pageid".$i.",";
            }

            $idString     = \rtrim($idString, ",");
            $whereParts[] = " id IN ({$idString}) ";

            $i = 0;

            foreach ($childPageIDs as $id) {
                $wherePrepared[] = [
                    'type'  => \PDO::PARAM_INT,
                    'value' => $id,
                    'name'  => ":pageid".$i
                ];
                $i++;
            }
        }

        $where = \implode(' OR ', $whereParts);

        // query
        $query = "
                SELECT id
                FROM {$table}
                WHERE active = 1 AND ({$where})
                ORDER BY release_from DESC, c_date DESC
            ";

        if ($feedLimit > 0) {
            $query .= "LIMIT :limit";
        }

        // search
        $Statement = $PDO->prepare($query);

        foreach ($wherePrepared as $prepared) {
            $Statement->bindValue(
                $prepared['name'],
                $prepared['value'],
                $prepared['type']
            );
        }

        if ($feedLimit > 0) {
            $Statement->bindValue(':limit', $feedLimit, \PDO::PARAM_INT);
        }

        $Statement->execute();
        $result = $Statement->fetchAll(\PDO::FETCH_ASSOC);

        $ids = [];

        foreach ($result as $row) {
            $ids[] = $row['id'];
        }

        return $ids;
    }

    /**
     * Parses the value of the site select control and returns all selected Site IDs
     * @param $siteSelectValue
     *
     * @return array
     * @throws QUI\Exception
     */
    public function parseSiteSelect($siteSelectValue)
    {
        $ids     = [];
        $Project = QUI::getProject(
            $this->getAttribute('project'),
            $this->getAttribute('lang')
        );

        // All sites, if no sites were selected.
        if (empty($siteSelectValue)) {
            $queryParams = [
                'order' => 'release_from DESC, c_date DESC'
            ];


            $ids = $Project->getSitesIds($queryParams);

            $ids = \array_map(function ($entry) {
                return (int)$entry['id'];
            }, $ids);

            return $ids;
        }

        // Get the IDs of the selected sites
        $PDO   = QUI::getPDO();
        $table = $Project->getAttribute('db_table');
        $sites = \explode(';', $siteSelectValue);

        $idCount  = 0;
        $strCount = 0;

        $whereParts    = [];
        $wherePrepared = [];

        $childPageIDs = [];
        foreach ($sites as $needle) {
            //
            if (\is_numeric($needle)) {
                $_id = ':id'.$idCount;

                $whereParts[] = " id = {$_id} ";

                $wherePrepared[] = [
                    'type'  => \PDO::PARAM_INT,
                    'value' => $needle,
                    'name'  => $_id
                ];

                $idCount++;
                continue;
            }

            // Search for children of this site
            if (\preg_match("~p[0-9]+~i", $needle)) {
                $parentSiteID = (int)\substr($needle, 1);
                $childPageIDs = \array_merge($childPageIDs, $Project->get($parentSiteID)->getChildrenIdsRecursive());
                continue;
            }

            // Search for type
            $_id = ':str'.$strCount;

            $whereParts[]    = " type LIKE {$_id} ";
            $wherePrepared[] = [
                'type'  => \PDO::PARAM_STR,
                'value' => $needle,
                'name'  => $_id
            ];

            $strCount++;
        }

        // Create the part of the query for the site ids of child sites.
        // `id` IN ( id1, id2, id3, id4 )
        if (!empty($childPageIDs)) {
            $childPageIDs = \array_unique($childPageIDs);

            $idString = "";

            for ($i = 0; $i < \count($childPageIDs); $i++) {
                $idString .= ":pageid".$i.",";
            }

            $idString = \rtrim($idString, ",");

            $whereParts[] = " id IN ({$idString}) ";

            $i = 0;
            foreach ($childPageIDs as $id) {
                $wherePrepared[] = [
                    'type'  => \PDO::PARAM_INT,
                    'value' => $id,
                    'name'  => ":pageid".$i
                ];
                $i++;
            }
        }

        $where = \implode(' OR ', $whereParts);

        // query
        $query = "
                SELECT id
                FROM {$table}
                WHERE active = 1 AND ({$where})
                ORDER BY release_from DESC, c_date DESC
            ";


        // search
        $Statement = $PDO->prepare($query);

        foreach ($wherePrepared as $prepared) {
            $Statement->bindValue(
                $prepared['name'],
                $prepared['value'],
                $prepared['type']
            );
        }


        $Statement->execute();
        $result = $Statement->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $row) {
            $ids[] = $row['id'];
        }

        return $ids;
    }

    /**
     * Returns the number of pages of this feed.
     *
     * @return int - Returns the number of pages or 0 if nor pages are used
     *
     * @throws QUI\Exception
     */
    public function getPageCount()
    {
        if (!$this->getAttribute("pageSize")) {
            return 0;
        }

        $pageSize   = $this->getAttribute("pageSize");
        $totalItems = \count($this->getSiteIDs());

        return \ceil($totalItems / $pageSize);
    }
}
