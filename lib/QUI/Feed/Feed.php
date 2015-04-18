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
     * @var Integer
     */
    protected $_feedId;

    /**
     * Constructor
     *
     * @param Integer $feedId
     *
     * @throws QUI\Exception
     */
    public function __construct($feedId)
    {
        $this->_feedId = (int)$feedId;

        $data = QUI::getDataBase()->fetch(array(
            'from'  => QUI::getDBTableName(Manager::TABLE),
            'where' => array(
                'id' => $this->_feedId
            ),
            'limit' => 1
        ));

        if (!isset($data[0])) {
            throw new QUI\Exception(
                'Feed not found'
            );
        }

        $this->setAttributes($data[0]);
    }

    /**
     * Return the Feed-ID
     *
     * @return Integer
     */
    public function getId()
    {
        return $this->_feedId;
    }

    /**
     * Return the feed tyoe
     *
     * @return String (rss|atom|googleSitemap)
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
     * @return Array
     */
    public function getAttributes()
    {
        $attributes = parent::getAttributes();
        $attributes['id'] = $this->_feedId;

        return $attributes;
    }

    /**
     * Save the feed
     */
    public function save()
    {
        $table = QUI::getDBTableName(Manager::TABLE);

        $feedlimit = (int)$this->getAttribute('feedlimit');
        $feedName = '';
        $feedDescription = '';

        if ($this->getAttribute('feedName')) {
            $feedName = Orthos::clear($this->getAttribute('feedName'));
        }

        if ($this->getAttribute('feedDescription')) {
            $feedDescription
                = Orthos::clear($this->getAttribute('feedDescription'));
        }

        QUI::getDataBase()->update($table, array(
            'project'         => $this->getAttribute('project'),
            'lang'            => $this->getAttribute('lang'),
            'feedtype'        => $this->getFeedType(),
            'feedsites'       => $this->getAttribute('feedsites'),
            'feedlimit'       => $feedlimit ? $feedlimit : '',
            'feedName'        => $feedName,
            'feedDescription' => $feedDescription
        ), array(
            'id' => $this->getId()
        ));

        // clear cache
        QUI\Cache\Manager::clear('quiqqer/feed/'.$this->getId());
    }

    /**
     * Output the feed as XML
     *
     * @return String
     */
    public function output()
    {
        $feedType = $this->getFeedType();
        $feedSites = $this->getAttribute('feedsites');
        $feedLimit = (int)$this->getAttribute('feedlimit');

        if (!$feedLimit) {
            $feedLimit = 10;
        }

        $Project = QUI::getProject(
            $this->getAttribute('project'),
            $this->getAttribute('lang')
        );

        $projectHost = $Project->getVHost(true, true);
        $feedUrl = $projectHost.URL_DIR.'feed='.$this->getId().'.rss';


        switch ($feedType) {
            case 'atom':
                $Feed = new Atom();
                break;

            // @todo more thang 20k sites
            case 'googleSitemap':
                $Feed = new GoogleSitemap();
                break;

            default:
                $Feed = new RSS();
                break;
        }

        $Channel = $Feed->createChannel();

        $Channel->setLanguage($Project->getLang());
        $Channel->setHost($projectHost.URL_DIR);

        $Channel->setAttribute('link', $feedUrl);
        $Channel->setAttribute('description',
            $this->getAttribute('feedDescription'));
        $Channel->setAttribute('title', $this->getAttribute('feedName'));
        $Channel->setDate(time());


        // search children
        if (empty($feedSites)) {
            if ($feedLimit < 1) {
                $ids = $Project->getSitesIds(array(
                    'order' => 'release_from DESC, c_date DESC'
                ));

            } else {
                $ids = $Project->getSitesIds(array(
                    'limit' => $feedLimit,
                    'order' => 'release_from DESC, c_date DESC'
                ));
            }

            $ids = array_map(function ($entry) {
                return (int)$entry['id'];
            }, $ids);

        } else {
            // search selected sites
            $PDO = QUI::getPDO();
            $table = $Project->getAttribute('db_table');
            $feedSites = explode(';', $feedSites);

            $idCount = 0;
            $strCount = 0;

            $whereParts = array();
            $wherePrepared = array();

            foreach ($feedSites as $param) {
                if (is_numeric($param)) {
                    $_id = ':id'.$idCount;

                    $whereParts[] = " id = {$_id} ";

                    $wherePrepared[] = array(
                        'type'  => \PDO::PARAM_INT,
                        'value' => $param,
                        'name'  => $_id
                    );

                    $idCount++;
                    continue;
                }

                $_id = ':str'.$strCount;

                $whereParts[] = " type LIKE {$_id} ";
                $wherePrepared[] = array(
                    'type'  => \PDO::PARAM_STR,
                    'value' => $param,
                    'name'  => $_id
                );

                $strCount++;
            }

            $where = implode(' OR ', $whereParts);

            // query
            $query
                = "
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

            $ids = array_map(function ($entry) {
                return (int)$entry['id'];
            }, $result);
        }


        // create feed
        foreach ($ids as $id) {
            try {
                $Site = $Project->get($id);
                $date = $Site->getAttribute('release_from');

                $url = $Site->getUrlRewrited();

                if ($date == '0000-00-00 00:00:00') {
                    $date = $Site->getAttribute('c_date');
                }

                $Item = $Channel->createItem(array(
                    'title'       => $Site->getAttribute('title'),
                    'description' => $Site->getAttribute('short'),
                    'language'    => $Project->getLang(),
                    'date'        => strtotime($date),
                    'link'        => $projectHost.URL_DIR.$url,
                    'permalink'   => $projectHost.URL_DIR.$Site->getCanonical()
                ));

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
}
