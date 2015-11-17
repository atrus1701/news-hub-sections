

jQuery(document).ready( function() {
	jQuery( document ).on( 'widget-added', SectionListing.widget_updated );
	jQuery( document ).on( 'widget-updated', SectionListing.widget_updated );
	jQuery( document ).on( 'widget-synced', SectionListing.widget_updated );
	jQuery( '.widget' ).each( function() {
		jQuery( document ).trigger( 'widget-updated', [ this ] );
	});
});



var SectionListing = {

	widget_updated: function( event, self ) {

		if( ! jQuery(self).find( '.widget-content select[name^="widget-section_listing"]' ).length ) return;

		var section_select = jQuery(self).find( '.widget-content select[name^="widget-section_listing"]' ).get(0);
		var items_select = jQuery(self).find( '.widget-content select[name^="widget-section_listing"]' ).get(1);
		var hidden_input = jQuery(self).find( '.widget-content .section-data' );
		var title = jQuery(self).find( '.widget-top h4 .in-widget-title' );
		var post_list = jQuery(self).find( '.widget-content .post-list' );
		var section_select_name = jQuery(section_select).attr( 'name' );
		var name = section_select_name.split( '][' );
		name = name[0] + ']';

		var update_button = jQuery(self).find( '.widget-content button' );
		jQuery(update_button).click( function(e) {

			e.preventDefault();
			SectionListing.update_post_edit( section_select, items_select, hidden_input, title, post_list, name );
			return false;

		});

		jQuery(self).find( '.widget-control-save' ).show();
		event.preventDefault();
		return false;
	},

	update_post_edit: function( section_select, items_select, hidden_input, title, post_list, name ) {

		var current_section = jQuery(section_select).find( ':selected' ).val();

		// setup up AJAX data.
		var data = {};
		data['action'] = 'news-hub-sections';
		data['ajax-action'] = 'get-post-list';
		data['section'] = current_section;

		// perform the AJAX request.
		jQuery.ajax({
			type: 'GET',
			url: ajaxurl,
			data: data,
			dataType: 'json'
		})
		.done(function( data )
		{
			if ( !data || !data.status )
			{
				if( data.message ) alert( data.message );
				else alert( 'An error occurred while getting the post list.' );
				return;
			}

			var hidden_input_values = jQuery( hidden_input ).val().split( ',' );
			var previous_section = hidden_input_values[0];
			var previous_items = hidden_input_values[1];

			previous_selected_items = [];
			if ( previous_section == current_section )
			{
				var post_list_selects = jQuery(post_list).find( 'select' );
				for ( var i = 0; i < post_list_selects.length; i++ )
				{
					previous_selected_items.push( jQuery( post_list_selects[i] ).find( ':selected' ).val() );
				}
			}

			var current_items = parseInt( jQuery( items_select ).find( ':selected' ).val() );

			jQuery( post_list ).html( '' );

			for ( var i = 0; i < current_items; i++ )
			{
				var selected_item_id = -1;
				if ( previous_selected_items.length > i ) {
					selected_item_id = parseInt( previous_selected_items[i] );
				}

				var html = '<p>';
				html += '<select name="' + name + '[posts][' + i + ']" class="widefat">';

				html += '<option value="-1" ';
				if ( selected_item_id == -1 )
					html += 'selected="selected" ';
				html += '>-- Latest Post --</option>';

				for ( var j = 0; j < data.posts.length; j++ )
				{
					var post = data.posts[j];
					html += '<option value="' + post.ID + '" ';
					if ( selected_item_id == post.ID )
						html += 'selected="selected" ';
					html += '>'+post.post_title+'</option>';
				}
				html += '</select>';
				html += '</p>';

				jQuery( post_list ).append( html );
			}

			jQuery( hidden_input ).val( current_section + ',' + current_items );
		})
		.fail(function( jqXHR, textStatus )
		{
			alert( "Failed to obtain section post list:\n" + jqXHR.responseText + ': ' + textStatus );
		});

	}

};

