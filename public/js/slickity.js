( function( $ ) {
  // Lightbox functionality
  $( '.slickity-lightbox' ).click( function() {
    $( '#slickity-lightbox-container-' + $( this ).data( 'id' ) )
      .toggleClass( 'slickity-lightbox-container--active' );
  });

  $( '.slickity-lightbox-container' ).click( function() {
    $( this ).removeClass( 'slickity-lightbox-container--active' );
  });

  $( '.slickity-lightbox-container .slickity' ).click( function( e ) {
    e.stopPropagation();
  });
})( jQuery );
