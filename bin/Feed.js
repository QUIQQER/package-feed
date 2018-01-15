/**
 * Feed settings
 *
 * @module package/quiqqer/feed/bin/Feed
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/controls/Control
 * @require utils/Controls
 * @require Ajax
 * @require Locale
 * @require css!package/quiqqer/feed/bin/Feed
 */

define('package/quiqqer/feed/bin/Feed', [

    'qui/QUI',
    'qui/controls/Control',
    'utils/Controls',
    'Ajax',
    'Locale',
    'Mustache',

    'text!package/quiqqer/feed/bin/Feed.html',
    'css!package/quiqqer/feed/bin/Feed.css'

], function (QUI, QUIControl, ControlUtils, Ajax, QUILocale, Mustache,
             template) {
    'use strict';

    var lg = 'quiqqer/feed';

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/feed/bin/Feed',
        Binds  : [
            '$toogglePageSizeVisibility',
            '$togglePublishSitesVisibility',
            '$detectSplitOption'
        ],

        initialize: function (options) {
            // defaults
            this.setAttributes({
                feedId   : false,
                project  : '',
                lang     : '',
                feedsites: '',
                feedtype : 'rss',
                feedlimit: '10',
                pageSize : 0,
                publish  : 0
            });

            this.parent(options);

            this.$Feedtype = null;
            this.$Project = null;
            this.$Limit = null;
            this.$Sites = null;
            this.$Name = null;
            this.$Desc = null;
            this.$PageSize = null;
            this.$SplitCheckbox = null;
            this.$Image = null;
            this.$ImageButton = null;
            this.$PublishSiteSelect = null;
            this.$PublishSiteSelectContainer = null;

        },

        /**
         * create the DOMNode
         *
         * @return {HTMLElement}
         */
        create: function () {
            this.$Elm = new Element('div', {
                'class': 'qui-control-feed',
                html   : Mustache.render(template, {
                    locales: {
                        feedType               : QUILocale.get(lg, 'quiqqer.feed.feedtype'),
                        feedName               : QUILocale.get(lg, 'quiqqer.feed.feedName'),
                        feedDescription        : QUILocale.get(lg, 'quiqqer.feed.feedDescription'),
                        project                : QUILocale.get('quiqqer/system', 'project'),
                        feedlimit              : QUILocale.get(lg, 'quiqqer.feed.feedlimit'),
                        feedSites              : QUILocale.get(lg, 'quiqqer.feed.feedSites'),
                        feedSitesPlaceholder   : QUILocale.get(lg, 'quiqqer.feed.sites.placeholder'),
                        split                  : QUILocale.get(lg, 'quiqqer.feed.split'),
                        pagesize               : QUILocale.get(lg, 'quiqqer.feed.pageSize'),
                        publish                : QUILocale.get(lg, 'quiqqer.feed.publish'),
                        publishSitesPlaceholder: QUILocale.get(lg, 'quiqqer.feed.publish.sites.placeholder'),
                        feedImage              : QUILocale.get(lg, 'quiqqer.feed.image')
                    }
                })
            });

            this.$Feedtype = this.$Elm.getElement('[name="feedtype"]');
            this.$Project = this.$Elm.getElement('[name="project"]');
            this.$Limit = this.$Elm.getElement('[name="feedlimit"]');
            this.$Sites = this.$Elm.getElement('[name="feedsites"]');
            this.$Name = this.$Elm.getElement('[name="feedName"]');
            this.$Desc = this.$Elm.getElement('[name="feedDescription"]');
            this.$SplitCheckbox = this.$Elm.getElement('[name="split"]');
            this.$PageSize = this.$Elm.getElement('[name="pagesize"]');
            this.$PublishCheckbox = this.$Elm.getElement('[name="publish"]');
            this.$Image = this.$Elm.getElement('[name="feedImage"]');
            this.$ImageButton = this.$Elm.getElement('.qui-control-feed-btn-image');
            this.$PublishSiteSelect = this.$Elm.getElement('[name="publish-sites"]');
            this.$PublishSiteSelectContainer = this.$Elm.getElement('.qui-feed-publish-sites-container');

            var self = this;
            // Split Feed event
            self.$Elm.getElement('.qui-feed-feedwindow-split-label').addEvent('click', function () {
                self.$SplitCheckbox.checked = !self.$SplitCheckbox.checked;
                self.$toogglePageSizeVisibility();
            });
            this.$SplitCheckbox.addEvent('change', self.$toogglePageSizeVisibility);

            // Publish sites event
            self.$PublishCheckbox.addEvent('change', self.$togglePublishSitesVisibility);
            this.$Elm.getElement('.qui-feed-feedwindow-publish-label').addEvent('click', function () {
                self.$PublishCheckbox.checked = !self.$PublishCheckbox.checked;
                self.$togglePublishSitesVisibility();
            });

            // Project select event
            this.$Project.addEvent('change', function () {
                var TypeSelect = QUI.Controls.getById(
                  self.$Sites.get('data-quiid')
                );

                var ProjectSelect = QUI.Controls.getById(
                  self.$Project.get('data-quiid')
                );

                if (TypeSelect && ProjectSelect) {
                    ProjectSelect.getProjects().each(function (Project) {
                        TypeSelect.setProject(
                          Project.getName(),
                          Project.getLang()
                        );
                    });
                }

                var PublishSiteSelect = QUI.Controls.getById(self.$Elm.getElement('.qui-feed-publish-sites').get('data-quiid'));
                if (PublishSiteSelect && ProjectSelect) {
                    ProjectSelect.getProjects().each(function (Project) {
                        PublishSiteSelect.setProject(
                          Project.getName(),
                          Project.getLang()
                        );
                    });
                }
            });

            // Hide the split option, if it is not supported for the feed type
            this.$Feedtype.addEvent('change', self.$detectSplitOption);
            self.$detectSplitOption();

            // Image button event
            this.$ImageButton.addEvent('click', function () {
                require(['controls/projects/project/media/Popup'],
                  function (MediaWindow) {

                      var projectName = false;

                      var projectData = JSON.decode(self.$Project.value);
                      if (projectData &&
                          typeof projectData[0] !== 'undefined' &&
                          'project' in projectData[0]) {
                          projectName = projectData[0].project;
                      }

                      var Window = new MediaWindow({
                          project: projectName,
                          events : {
                              onSubmit: function (Popup, imageData) {
                                  self.$Image.value = imageData.url;
                              }
                          }
                      });
                      Window.open();

                  });
            });

            ControlUtils.parse(this.$Elm);
            QUI.parse(this.$Elm, function () {
                if (self.getAttribute('feedId')) {
                    self.refresh();
                }
            });

            return this.$Elm;
        },

        /**
         * refresh the data
         */
        refresh: function () {
            var self = this;
            Ajax.get('package_quiqqer_feed_ajax_getFeed', function (result) {
                var quiid, Cntrl;
                
                self.$Sites.value = result.feedsites;
                self.$Feedtype.value = result.feedtype;
                self.$Limit.value = result.feedlimit;
                self.$Name.value = result.feedName;
                self.$Desc.value = result.feedDescription;
                self.$PublishCheckbox.checked = result.publish === '1';
                self.$PublishSiteSelect.value = result.publish_sites;
                self.$Image.value = result.feedImage;

                if (result.pageSize > 0) {
                    self.$PageSize.value = result.pageSize;
                    self.$SplitCheckbox.checked = true;
                    self.$toogglePageSizeVisibility();
                }

                self.$detectSplitOption();
                self.$togglePublishSitesVisibility();

                self.$Project.value = JSON.encode([
                    {
                        project: result.project,
                        lang   : result.lang
                    }]);

                // project
                quiid = self.$Project.get('data-quiid');
                Cntrl = QUI.Controls.getById(quiid);

                if (result.project !== '') {
                    if (Cntrl) {
                        Cntrl.addProject(result.project, result.lang);
                    }
                }

                // site types
                quiid = self.$Sites.get('data-quiid');
                Cntrl = QUI.Controls.getById(quiid);

                self.$Sites.set('data-project', result.project);
                self.$Sites.set('data-lang', result.lang);

                if (Cntrl) {
                    Cntrl.setAttribute('placeholder', QUILocale.get(lg, 'quiqqer.feed.publish.sites.placeholder'));

                    Cntrl.setProject(result.project, result.lang);
                    Cntrl.setValue(self.$Sites.value);
                    Cntrl.refresh();
                }

                // Refresh the PublishSites control
                var PublishSitesControl = QUI.Controls.getById(self.$PublishSiteSelect.get('data-quiid'));
                if (PublishSitesControl) {
                    PublishSitesControl.setAttribute('placeholder', QUILocale.get(lg, 'quiqqer.feed.sites.placeholder'));
                    PublishSitesControl.setProject(result.project, result.lang);
                    PublishSitesControl.setValue(self.$PublishSiteSelect.value);
                    PublishSitesControl.refresh();
                }

            }, {
                'package': 'quiqqer/feed',
                feedId   : this.getAttribute('feedId')
            });
        },

        /**
         * Return the feed attributes
         *
         * @returns {Object}
         */
        getFeedData: function () {
            var project = JSON.decode(this.$Project.value);

            var projectName = '',
                projectLang = '';

            if (typeof project[0] !== 'undefined' && 'project' in project[0]) {
                projectName = project[0].project;
            }

            if (typeof project[0] !== 'undefined' && 'lang' in project[0]) {
                projectLang = project[0].lang;
            }

            var pageSize = 0;
            if (this.$SplitCheckbox.checked && this.$PageSize.value > 0) {
                pageSize = this.$PageSize.value;
            }

            return {
                project        : projectName,
                lang           : projectLang,
                feedsites      : this.$Sites.value,
                feedtype       : this.$Feedtype.value,
                feedlimit      : this.$Limit.value,
                feedName       : this.$Name.value,
                feedDescription: this.$Desc.value,
                pageSize       : pageSize,
                publish        : this.$PublishCheckbox.checked ? 1 : 0,
                publish_sites  : this.$PublishSiteSelect.value,
                feedImage      : this.$Image.value
            };
        },

        /**
         * Save the feed
         *
         * @param {Function} callback - [optional] callback function
         */
        save: function (callback) {
            Ajax.post('package_quiqqer_feed_ajax_setFeed', function () {
                if (typeof callback !== 'undefined') {
                    callback();
                }
            }, {
                'package': 'quiqqer/feed',
                feedId   : this.getAttribute('feedId'),
                params   : JSON.encode(this.getFeedData())
            });
        },

        /**
         * Toggles the visibility of the page size input field
         */
        $toogglePageSizeVisibility: function () {
            var pageSizeContainer = this.$Elm.getElement('.qui-feed-feetwindow-pagesize');

            if (this.$SplitCheckbox.checked) {
                pageSizeContainer.style.display = 'block';
            } else {
                pageSizeContainer.style.display = 'none';
            }
        },

        /**
         * Toggles the visibility of the input to select sites on which the feed should get displayed
         */
        $togglePublishSitesVisibility: function () {
            if (this.$PublishCheckbox.checked) {
                this.$PublishSiteSelectContainer.style.display = 'block';
            } else {
                this.$PublishSiteSelectContainer.style.display = 'none';
            }
        },

        /**
         * Hides/Shows the visiblity of thec checkbox to split the feed into pages, depending o the current selected feeds support for pagination
         */
        $detectSplitOption: function () {
            if (this.$Feedtype.value !== 'googleSitemap') {
                this.$SplitCheckbox.checked = false;
                this.$Elm.getElement('.qui-feed-feetwindow-pagesize').style.display = 'none';
                this.$Elm.getElement('.qui-feed-feedwindow-split').style.display = 'none';
            } else {
                this.$Elm.getElement('.qui-feed-feedwindow-split').style.display = 'block';
            }
        }
    });
});