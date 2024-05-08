wp.domReady( () => {

    // To list of all variations, run the following in the browser console.
    // console.table( wp.blocks.getBlockVariations( 'core/embed' ) );
    const allowedEmbedBlocks = [
        'vimeo',
        'youtube',
    ];

    wp.blocks.getBlockVariations( 'core/embed' ).forEach( function( blockVariation ) {
        if ( allowedEmbedBlocks.indexOf( blockVariation.name ) === -1 ) {
            wp.blocks.unregisterBlockVariation( 'core/embed', blockVariation.name );
        }
    } );
} );
