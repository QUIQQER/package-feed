
/**
 * Feed settings
 *
 * @module package/quiqqer/feed/bin/Feed
 * @author www.pcsg.de (Henning Leutz)
 */

define('package/quiqqer/feed/bin/Feed', [

    'qui/QUI',
    'qui/controls/Control',
    'utils/Controls',
    'Ajax',
    'Locale',

    'css!package/quiqqer/feed/bin/Feed'

], function(QUI, QUIControl, ControlUtils, Ajax, QUILocale)
{
    "use strict";

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
        },

        /**
         * create the DOMNode
         *
         * @return {DOMNode}
         */
        create : function()
        {
            this.$Elm = new Element('div', {
                'class' : 'qui-control-feed',
                html    : '<fieldset>'+
                              '<label>'+
                                  'Feed Art'+
                              '</label>'+
                              '<select name="feedtype">'+
                                  '<option value="rss">RSS</option>'+
                                  '<option value="atom">Atom</option>'+
                                  '<option value="googleSitemap">Google Sitemap</option>'+
                              '</select>'+
                          '</fieldset>'+
                          '<fieldset>'+
                              '<label>'+
                                  'Projekt'+
                              '</label>'+
                              '<input type="text"name="project" class="project"  />'+
                          '</fieldset>'+
                          '<fieldset>'+
                              '<label>'+
                                  'Max. Anzahl der Einträge'+
                              '</label>'+
                              '<input type="number" name="feedlimit" />'+
                          '</fieldset>'+
                          '<fieldset class="qui-control-feed-feedsites">'+
                              '<label>'+
                                  'Welche Seiten sollen in dem Feed enthalten sein?'+
                              '</label>'+
                              '<textarea name="feedsites"></textarea>'+
                          '</fieldset>'
            });

            ControlUtils.parse( this.$Elm );

            this.$Feedtype = this.$Elm.getElement( '[name="feedtype"]' );
            this.$Project  = this.$Elm.getElement( '[name="project"]' );
            this.$Limit    = this.$Elm.getElement( '[name="feedlimit"]' );
            this.$Sites    = this.$Elm.getElement( '[name="feedsites"]' );

            if ( this.getAttribute( 'feedId' ) ) {
                this.refresh();
            }

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
                console.warn( result );

                self.$Sites.value    = result.feedsites;
                self.$Feedtype.value = result.feedtype;
                self.$Limit.value    = result.feedlimit;

                var quiid = self.$Project.get( 'data-quiid'),
                    ProjectControl = QUI.Controls.getById( quiid );

                if ( result.project !== '' ) {
                    ProjectControl.addProject( result.project, result.lang );
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
                feedlimit : this.$Limit.value
            };
        },

        /**
         * Save the feed
         *
         * @param {Function} callback - [optional] callback function
         */
        save : function(callback)
        {
            console.log( this.getFeedData() );

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