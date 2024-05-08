/**
 * Admin scripts.
 *
 */

import '../styles/admin.scss';

/*global ajaxurl */

( function( $, acf, _, undef ) {
    /**
     * Search layout.
     *
     * @type {string}
     */
    const layoutEventSearch = 'event_search';
    /**
     * Selection layout.
     *
     * @type {string}
     */
    const layoutEventSelected = 'event_selected';

    /**
     * Carousel search layout.
     *
     * @type {string}
     */
    const layoutEventSearchCarousel = 'event_search_carousel';

    /**
     * Carousel selection layout.
     *
     * @type {string}
     */
    const layoutEventSelectedCarousel = 'event_selected_carousel';

    /**
     * Create query object
     *
     * @param {Array} fields ACF fields.
     *
     * @return {Object} Query
     */
    const createQueryObject = ( fields ) => {
        const query = {};

        fields.forEach( ( field ) => {
            query[ field.get( 'name' ) ] = field.val();
        } );

        return query;
    };

    /**
     * EventSearch factory
     *
     * @return {void}
     */
    function EventSearchFactory() {

        /**
         * Init
         *
         * @return {void}
         */
        const init = () => {
            $( '.acf-field-flexible-content .values .layout' ).each( ( idx, el ) => {
                maybeCreate( $( el ) );
            } );

            acf.addAction( 'append', ( $el ) => {
                maybeCreate( $el );
            } );
        };

        /**
         * Maybe create new EventSearch
         *
         * @param {HTMLElement|jQuery} $el jQuery selected
         *
         * @return {void}
         */
        const maybeCreate = ( $el ) => {
            const layout = $el.data( 'layout' ) || '';
            const layoutsWithSearch = [
                layoutEventSearch,
                layoutEventSelected,
                layoutEventSearchCarousel,
                layoutEventSelectedCarousel,
            ];

            if ( layoutsWithSearch.includes( layout ) ) {
                new EventSearch( $el );
            }
        };

        init();
    }

    /**
     * EventSearch ACF Layout
     *
     * @param {HTMLElement|jQuery} $el jQuery selected element
     *
     * @return {void}
     */
    function EventSearch( $el ) {

        /**
         * Date fields
         *
         * @type {{start: string, end: string}}
         */
        const dateFields = {
            start: '',
            end: '',
        };

        /**
         * Do search with debounce
         *
         * @return {void}
         */
        const doSearchWithDebounce = _.debounce( ( e ) => {
            doSearch( e );
        }, 1000 );

        /**
         * Init
         *
         * @return {void}
         */
        const init = () => {
            $el.on( 'input', 'input', ( e ) => {
                doSearchWithDebounce( e );
            } );
            $el.on( 'change', 'select', ( e ) => {
                doSearchWithDebounce( e );
            } );

            setInterval( () => {
                checkDateTimeChanges();
            }, 500 );

            const layout = $el.data( 'layout' );

            if ( layout === layoutEventSelected || layout === layoutEventSelectedCarousel ) {
                const fields = acf.getFields( { parent: $el } );
                const resultLink = fields.find( ( field ) => field.get( 'name' ) === 'result_link' );
                const selectedEvents = fields.find( ( field ) => field.get( 'name' ) === 'selected_events' );

                resultLink.$el.on( 'change', ( e ) => {
                    if ( e.target.value.length > 0 ) { // skip when resultLink gets emptied
                        const queryFields = createQueryObject( fields );
                        selectedEvents.data.query = queryFields;
                        selectedEvents.fetch( queryFields );
                    }
                } );
            }
        };

        /**
         * Check date time field changes
         *
         * @return {void}
         */
        const checkDateTimeChanges = () => {
            const fields = acf.getFields( { parent: $el } );
            const start = fields.find( ( field ) => field.get( 'name' ) === 'start_date' );
            const end = fields.find( ( field ) => field.get( 'name' ) === 'end_date' );
            let search = false;

            if ( start.val() !== dateFields.start || end.val() !== dateFields.end ) {
                search = true;
                dateFields.start = start.val();
                dateFields.end = end.val();
            }

            if ( search ) {
                doSearch();
            }
        };

        /**
         * Generate result link value
         *
         * @param {string} link Link.
         * @return {string} R
         */
        const generateResultLinkValue = ( link ) => {
            const { searchResultsLink } = adminData; // eslint-disable-line

            // bail early if search result link empty
            if ( _.isNull( searchResultsLink ) ) {
                return link;
            }

            const queryParams = link.split( '?' )[ 1 ];

            if ( _.isUndefined( queryParams ) ) {
                return link;
            }

            return ` ${ searchResultsLink }?${ queryParams } `;
        };

        /**
         * Search
         *
         * @param {Object} e Event.
         * @return {void}
         */
        const doSearch = ( e ) => {
            if (
                ! _.isUndefined( e ) &&
                $( e.currentTarget ).closest( '.acf-field' ).hasClass( 'no-search' ) // prevent search if input field value not search related
            ) {
                return;
            }

            const fields = acf.getFields( {
                parent: $el,
            } );
            const resultCount = fields.find( ( field ) => field.get( 'name' ) === 'result_count' );
            const resultLink = fields.find( ( field ) => field.get( 'name' ) === 'result_link' );

            resultLink.val( '' );
            resultCount.val( '' );

            acf.showLoading( $el );

            $.ajax( {
                type: 'get',
                url: ajaxurl,
                data: {
                    action: 'event_search',
                    params: createQueryObject( fields ),
                },
                success: ( response ) => {
                    if ( response && response.data ) {
                        const resultLinkValue = generateResultLinkValue( response.data.url );
                        resultLink.val( resultLinkValue );
                        resultCount.val( response.data.count );
                    }
                },
                complete: () => {
                    acf.hideLoading( $el );
                },
            } );
        };

        init();
    }

    $( document ).ready( () => {
        new EventSearchFactory();
    } );

    const Field = acf.Field.extend( {
        type: 'rest_relationship',
        events: {
            'click .choices-list .acf-rel-item': 'onClickAdd',
            'click [data-name="remove_item"]': 'onClickRemove',
        },

        $control() {
            return this.$( '.acf-rest-relationship' );
        },

        $list( list ) {
            return this.$( `.${ list }-list` );
        },

        $listItems( list ) {
            return this.$list( list ).find( '.acf-rel-item' );
        },

        $listItem( list, id ) {
            return this.$list( list ).find( `.acf-rel-item[data-id="${ id }"]` );
        },

        getValue() {
            const val = [];
            this.$listItems( 'values' ).each( function() {
                val.push( $( this ).data( 'id' ) );
            } );
            return val.length ? val : false;
        },

        newChoice( props ) {
            return `<li><span data-id="${ props.id }" class="acf-rel-item">${ props.text }</span></li>`;
        },

        newValue( props ) {
            const name = this.getInputName();
            const id = props.id;
            const text = props.text || id;

            const input = `<input type="hidden" name="${ name }[${ id }]" value="${ text }" />`;
            const removeItem = '<a href="#" class="acf-icon -minus small dark" data-name="remove_item"></a>';

            return `<li>${ input }<span data-id="${ id }" class="acf-rel-item">${ text } ${ removeItem }</span></li>`;
        },

        initialize() {
            // Delay initialization until "interacted with" or "in view".
            const delayed = this.proxy( acf.once( function() {
                // Add sortable.
                this.$list( 'values' ).sortable( {
                    items: 'li',
                    forceHelperSize: true,
                    forcePlaceholderSize: true,
                    scroll: true,
                    update: this.proxy( function() {
                        this.$input().trigger( 'change' );
                    } ),
                } );

                // Avoid browser remembering old scroll position and add event.
                this.$list( 'choices' ).scrollTop( 0 );

                const delayedFields = acf.getFields( { parent: this.$el.parent() } );
                const queryFields = createQueryObject( delayedFields );

                queryFields.action = 'acf/fields/rest_relationship/query';
                queryFields.field_key = this.get( 'key' );

                // Fetch choices.
                this.fetch( queryFields );
            } ) );

            // Bind "interacted with".
            this.$el.one( 'mouseover', delayed );
            this.$el.one( 'focus', 'input', delayed );

            // Bind "in view".
            acf.onceInView( this.$el, delayed );
        },

        onClickAdd( e, $el ) {

            const max = parseInt( this.get( 'max' ).toString(), 10 );
            const amountOfSelectedEvents = parseInt( $el.closest( '.choices' ).next().find( 'li' ).length, 10 ) + 1;

            // can be added?
            if ( $el.hasClass( 'disabled' ) ) {
                return false;
            }

            // validate
            if ( amountOfSelectedEvents >= max ) {

                // add notice
                this.showNotice( {
                    text: acf.__( 'Maximum values reached ( {max} values )' ).replace( '{max}', max ),
                    type: 'warning',
                } );
                return false;
            }

            // disable
            $el.addClass( 'disabled' );

            // add
            const html = this.newValue( {
                id: $el.data( 'id' ),
                text: $el.html(),
            } );
            this.$list( 'values' ).append( html );

            // trigger change
            this.$input().trigger( 'change' );
        },

        onClickRemove( e, $el ) {

            // Prevent default here because generic handler wont be triggered.
            e.preventDefault();

            // vars
            const $span = $el.parent();
            const $li = $span.parent();
            const id = $span.data( 'id' );

            // remove value
            $li.remove();

            // show choice
            this.$listItem( 'choices', id ).removeClass( 'disabled' );

            // trigger change
            this.$input().trigger( 'change' );
        },

        maybeFetch() {

            // vars
            let timeout = this.get( 'timeout' );

            // abort timeout
            if ( timeout ) {
                clearTimeout( timeout );
            }

            // fetch
            timeout = this.setTimeout( this.fetch, 300 );
            this.set( 'timeout', timeout );
        },

        getAjaxData( passedFields = undef ) {

            // load data based on element attributes
            let ajaxData = this.$control().data() || {};
            for ( const name in ajaxData ) {
                if ( ajaxData.hasOwnProperty( name ) ) {
                    ajaxData[ name ] = this.get( name );
                }
            }

            if ( passedFields !== typeof undefined ) {
                ajaxData.query = ajaxData.query || {};
                for ( const passedFieldsKey in passedFields ) {
                    if ( passedFields.hasOwnProperty( passedFieldsKey ) ) {
                        ajaxData.query[ passedFieldsKey ] = passedFields[ passedFieldsKey ];
                    }
                }
            }

            // extra
            ajaxData.action = 'acf/fields/rest_relationship/query';
            ajaxData.field_key = this.get( 'key' );

            // Filter.
            ajaxData = acf.applyFilters( 'rest_relationship_ajax_data', ajaxData, this );

            // return
            return ajaxData;
        },

        fetch( passedFields = undef ) {
            // abort XHR if this field is already loading AJAX data
            if ( this.get( 'xhr' ) ) {
                this.get( 'xhr' ).abort();
            }

            // add to this.o
            const ajaxData = this.getAjaxData( passedFields );

            // clear html if is new query
            const $choicesList = this.$list( 'choices' );
            if ( parseInt( ajaxData.paged ) === 1 ) {
                $choicesList.html( '' );
            }

            const $loading = $( `<li><i class="acf-loading"></i> ${ acf.__( 'Loading' ) }</li>` );
            $choicesList.append( $loading );
            this.set( 'loading', true );

            // callback
            const onComplete = function() {
                this.set( 'loading', false );
                $loading.remove();
            };

            const onSuccess = function( json ) {
                // no results
                if ( ! json || ! json.results || ! json.results.length ) {

                    // prevent pagination
                    this.set( 'more', false );

                    // add message
                    if ( parseInt( this.get( 'paged' ) ) === 1 ) {
                        this.$list( 'choices' ).append( `<li>${ acf.__( 'No matches found' ) }</li>` );
                    }

                    // return
                    return;
                }

                // set more (allows pagination scroll)
                this.set( 'more', json.more || false );

                // get new results
                const html = this.walkChoices( json.results );
                const $html = $( html );

                // apply .disabled to left li's
                const val = this.val();
                if ( val && val.length ) {
                    val.map( ( id ) => {
                        return $html.find( '.acf-rel-item[data-id="' + id + '"]' ).addClass( 'disabled' );
                    } );
                }

                // append
                $choicesList.empty();
                $choicesList.append( $html );

                // merge together groups
                let $prevLabel = false;
                let $prevList = false;

                $choicesList.find( '.acf-rel-label' ).each( function() {

                    const $label = $( this );
                    const $list = $label.siblings( 'ul' );

                    // eslint-disable-next-line eqeqeq
                    if ( $prevLabel && $prevLabel.text() == $label.text() ) {
                        $prevList.append( $list.children() );
                        $( this ).parent().remove();
                        return;
                    }

                    // update vars
                    $prevLabel = $label;
                    $prevList = $list;
                } );
            };

            /**
             * get results
             *
             * @type {*|jQuery}
             */
            const results = $.ajax( {
                url: acf.get( 'ajaxurl' ),
                dataType: 'json',
                type: 'post',
                data: acf.prepareForAjax( ajaxData ),
                context: this,
                success: onSuccess,
                complete: onComplete,
            } );

            // set
            this.set( 'xhr', results );
        },

        walkChoices( data ) {

            /**
             * This is our deduplication object.
             *
             * @type {{}}
             */
            const knownElements = {};

            // walker
            const walk = function( walkerData, known = {} ) {

                // vars
                let html = '';

                // is array
                if ( $.isArray( walkerData ) ) {
                    walkerData.map( ( item ) => {
                        html += walk( item );
                        return html;
                    } );

                    // is item
                }
                else if ( $.isPlainObject( walkerData ) ) {
                    const wId = acf.escAttr( walkerData.id );
                    const wTxt = acf.escHtml( walkerData.text );

                    // group
                    if ( walkerData.children !== undef ) {

                        // If there's no unique children, no need for the parent either
                        const children = walk( walkerData.children );

                        if ( children.length > 0 ) {
                            html += `<li><span class="acf-rel-label">${ wTxt }</span>`;
                            html += `<ul class="acf-bl">${ children }</ul></li>`;
                        }

                        // single
                    }
                    else if ( known[ walkerData.id ] === undef ) {
                        html += `<li><span class="acf-rel-item" data-id="${ wId }">${ wTxt }</span></li>`;

                        known[ walkerData.id ] = walkerData.id;
                    }
                }

                // return
                return html;
            };

            return walk( data, knownElements );
        },

    } );

    acf.registerFieldType( Field );

}( jQuery, window.acf, window._, undefined ) );
