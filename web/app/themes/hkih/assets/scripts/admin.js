( function( $ ) {
    const { adminajax } = adminData; // eslint-disable-line

    $( 'body' ).on( 'click', '.editor-preview-dropdown__toggle', function() {
        // hide preview dropdown
        $( this ).removeAttr( 'aria-haspopup aria-expanded' );
        $( '.components-dropdown__content' ).hide();

        $.ajax( {
            type: 'get',
            url: adminajax,
            data: {
                action: 'create_preview_request',
            },
            success: ( response ) => {
                if ( response.success ) {
                    $( response.data ).appendTo( 'body' ).submit().remove();
                }

                if ( ! response.success ) {
                    const error = `<div class="components-notice-list">
                        <div class="components-notice is-error">
                            <div class="components-notice__content">${ response.data }</div>
                        </div>
                    </div>`;

                    $( '.interface-interface-skeleton__content' ).prepend( error );
                }
            },
        } );
    } );
}( jQuery, window._ ) );
