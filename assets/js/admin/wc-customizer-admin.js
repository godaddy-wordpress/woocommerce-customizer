jQuery( document ).ready( function( $ ) {
    // Store the media library "select media" frame.
    var select_frame;

    jQuery('#media_library_button').click( function( event ){
        event.preventDefault();

        // Create the media library frame.
        select_frame = wp.media.frames.select_frame = wp.media({
            frame: 'select',
            title: 'Select an image',
            button: {
                text: 'Use this image'
            },
            // We only want one image from the media library.
            multiple: false
        });

        // When an image is selected, update the image preview.
        select_frame.on( 'select', function() {
            var image = select_frame.state().get('selection').first().toJSON();
            $( '#media_preview' ).attr( 'src', image.url ).css( 'width', 'auto' );
            // Store the selected image URL to be saved via the settings form.
            $( '#woocommerce_placeholder_img_src' ).val( image.url );
        });

        // Open the media library model.
        select_frame.open();
    });

});
