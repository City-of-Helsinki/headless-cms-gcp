( function( $, _ ) {
    const { externalUrl } = adminData; // eslint-disable-line

    $( 'body' ).on( 'click', '.block-editor-post-preview__button-toggle', function() {

        // bail early if external url not set
        if ( _.isNull( externalUrl ) ) {
            return;
        }

        // hide preview dropdown
        $( this ).removeAttr( 'aria-haspopup aria-expanded' );
        $( '.block-editor-post-preview__dropdown-content' ).hide();

        window.open( externalUrl, '_blank' );
    } );

}( jQuery, window._ ) );
