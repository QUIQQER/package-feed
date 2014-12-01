
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

    'css!package/quiqqer/feed/bin/Feed.css'

], function(QUI, QUIControl, ControlUtils, Ajax, QUILocale)
{
    "use strict";

    var lg = 'quiqqer/feed';

    return new Class({

        Extends : QUIControl,
        Type    : 'package/quiqqer/feed/bin/Feed',

        initialize : function(options)
        {
            // defaults
            this.setAttributes({
                feedId    : false,
                project   : '',
                lang      : '',
                feedsites : '',
                feedtype  : 'rss',
                feedlimit : '10'
            });

            this.parent( options );

            this.$Feedtype = null;
            this.$Project  = null;
            this.$Limit    = null;
            this.$Sites    = null;
            this.$Name     = null;
            this.$Desc     = null;
        },

        /**
         * create the DOMNode
         *
         * @return {HTMLElement}
         */
        create : function()
        {
            this.$Elm = new Element('div', {
                'class' : 'qui-control-feed',
                html    : '<fieldset>'+
                              '<label>'+
                                  QUILocale.get( lg, 'quiqqer.feed.feedtype' ) +
                              '</label>'+
                              '<select name="feedtype">'+
                                  '<option value="rss">RSS</option>'+
                                  '<option value="atom">Atom</option>'+
                                  '<option value="googleSitemap">Google Sitemap</option>'+
                              '</select>'+
                          '</fieldset>'+
                          '<fieldset>'+
                              '<label>'+
                                  QUILocale.get( lg, 'quiqqer.feed.feedName' ) +
                              '</label>'+
                              '<input type="text" name="feedName"  />'+
                          '</fieldset>'+
                          '<fieldset>'+
                              '<label>'+
                                  QUILocale.get( lg, 'quiqqer.feed.feedDescription' ) +
                              '</label>'+
                              '<input type="text" name="feedDescription"  />'+
                          '</fieldset>'+
                          '<fieldset>'+
                              '<label>'+
                                  QUILocale.get( 'quiqqer/system', 'project' ) +
                              '</label>'+
                              '<div class="qui-control-feed-project">'+
                                  '<input type="text" name="project" class="project"  />'+
                              '</div>'+
                          '</fieldset>'+
                          '<fieldset>'+
                              '<label>'+
                                  QUILocale.get( lg, 'quiqqer.feed.feedlimit' ) +
                              '</label>'+
                              '<input type="number" name="feedlimit" />'+
                          '</fieldset>'+
                          '<fieldset class="qui-control-feed-feedsites">'+
                              '<label>'+
                                  'Welche Seiten sollen in dem Feed enthalten sein?'+
                              '</label>'+
                              '<input name="feedsites" ' +
                '                     data-qui="controls/projects/project/site/Select" ' +
                '                     data-project="" data-lang="" ' +
                '              />'+
                          '</fieldset>'
            });

            this.$Feedtype = this.$Elm.getElement( '[name="feedtype"]' );
            this.$Project  = this.$Elm.getElement( '[name="project"]' );
            this.$Limit    = this.$Elm.getElement( '[name="feedlimit"]' );
            this.$Sites    = this.$Elm.getElement( '[name="feedsites"]' );
            this.$Name     = this.$Elm.getElement( '[name="feedName"]' );
            this.$Desc     = this.$Elm.getElement( '[name="feedDescription"]' );

            var self = this;

            this.$Project.addEvent('change', function()
            {
                var TypeSelect = QUI.Controls.getById(
                    self.$Sites.get( 'data-quiid')
                );

                var ProjectSelect = QUI.Controls.getById(
                    self.$Project.get( 'data-quiid')
                );

                if ( TypeSelect && ProjectSelect )
                {
                    ProjectSelect.getProjects().each(function(Project)
                    {
                        TypeSelect.setProject(
                            Project.getName(),
                            Project.getLang()
                        );
                    });
                }
            });


            ControlUtils.parse( this.$Elm );

            QUI.parse(this.$Elm, function()
            {
                if ( self.getAttribute( 'feedId' ) ) {
                    self.refresh();
                }
            });

            return this.$Elm;
        },

        /**
         * refresh the data
         */
        refresh : function()
        {
            var self = this;

            Ajax.get('package_quiqqer_feed_ajax_getFeed', function(result)
            {
                var quiid, Cntrl;

                self.$Sites.value    = result.feedsites;
                self.$Feedtype.value = result.feedtype;
                self.$Limit.value    = result.feedlimit;
                self.$Name.value     = result.feedName;
                self.$Desc.value     = result.feedDescription;

                self.$Project.value = JSON.encode([{
                    project : result.project,
                    lang    : result.lang
                }]);

                // project
                quiid = self.$Project.get( 'data-quiid');
                Cntrl = QUI.Controls.getById( quiid );

                if ( result.project !== '' )
                {
                    if ( Cntrl ) {
                        Cntrl.addProject( result.project, result.lang );
                    }
                }

                // site types
                quiid = self.$Sites.get( 'data-quiid');
                Cntrl = QUI.Controls.getById( quiid );

                self.$Sites.set( 'data-project', result.project );
                self.$Sites.set( 'data-lang', result.lang );

                if ( Cntrl )
                {
                    Cntrl.setAttribute(
                        'placeholder',
                        'Wenn Sie keine Einstellungen tätigen, werden im Projekt alle Seiten beachtet'
                    );

                    Cntrl.setProject( result.project, result.lang );
                    Cntrl.setValue( self.$Sites.value );
                    Cntrl.refresh();
                }

            }, {
                'package' : 'quiqqer/feed',
                feedId    : this.getAttribute( 'feedId' )
            });
        },

        /**
         * Return the feed attributes
         *
         * @returns {Object}
         */
        getFeedData : function()
        {
            var project = JSON.decode( this.$Project.value );

            var projectName = '',
                projectLang = '';

            if ( typeof project[0] !== 'undefined' && "project" in project[0] ) {
                projectName = project[0].project;
            }

            if ( typeof project[0] !== 'undefined' && "lang" in project[0] ) {
                projectLang = project[0].lang;
            }

            return {
                project   : projectName,
                lang      : projectLang,
                feedsites : this.$Sites.value,
                feedtype  : this.$Feedtype.value,
                feedlimit : this.$Limit.value,
                feedName  : this.$Name.value,
                feedDescription : this.$Desc.value
            };
        },

        /**
         * Save the feed
         *
         * @param {Function} callback - [optional] callback function
         */
        save : function(callback)
        {
            Ajax.post('package_quiqqer_feed_ajax_setFeed', function()
            {
                if ( typeof callback !== 'undefined' ) {
                    callback();
                }
            }, {
                'package' : 'quiqqer/feed',
                feedId    : this.getAttribute( 'feedId' ),
                params    : JSON.encode( this.getFeedData() )
            });
        }
    });
});