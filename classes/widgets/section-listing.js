

jQuery.expr[':'].regex = function(elem, index, match) {
    var matchParams = match[3].split(','),
        validLabels = /^(data|css):/,
        attr = {
            method: matchParams[0].match(validLabels) ? 
                        matchParams[0].split(':')[0] : 'attr',
            property: matchParams.shift().replace(validLabels,'')
        },
        regexFlags = 'ig',
        regex = new RegExp(matchParams.join('').replace(/^s+|s+$/g,''), regexFlags);
    return regex.test(jQuery(elem)[attr.method](attr.property));
}


jQuery(document).ready(
	function()
	{
		// setup the Section Listing widgets
		jQuery("div:regex(id,widget-([0-9]+)_section_listing-([0-9]+))").SectionListingWidgetEditor();
	}
);


(function( $ ) {
	
	/**
	 * 
	 * 
	 * @package    news-hub-sections
	 * @author     Crystal Barton <atrus1701@gmail.com>
	 */
	$.fn.SectionListingWidgetEditor = function()
	{

		/**
		 * 
		 */
		return this.each(function()
		{
			$( document ).on( 'widget-updated', widget_updated );
			$( document ).on( 'widget-synced', [this, []] );
			$( document ).trigger( 'widget-updated', [this] );
		});


		/**
		 * 
		 */
		function widget_updated( event, self )
		{
			var section_select = $(self).find('.widget-content select[name^="widget-section_listing"]').get(0);
			var items_select = $(self).find('.widget-content select[name^="widget-section_listing"]').get(1);
			var hidden_input = $(self).find('.widget-content .section-data');
			var title = $(self).find('.widget-top h4 .in-widget-title');
			var post_list = $(self).find('.widget-content .post-list');
			var section_select_name = $(section_select).attr('name');
			var name = section_select_name.split('][');
			name = name[0] + ']';

			var update_button = $(self).find('.widget-content button');
			$(update_button).click( function(e) {

				e.preventDefault();
				update_post_edit( section_select, items_select, hidden_input, title, post_list, name );
				return false;

			});

			$(self).find('.widget-control-save').show();
		}


		/**
		 * 
		 */
		function update_post_edit( section_select, items_select, hidden_input, title, post_list, name )
		{
			var current_section = $(section_select).find(':selected').val();

			// setup up AJAX data.
			var data = {};
			data['action'] = 'news-hub-sections';
			data['ajax-action'] = 'get-post-list';
			data['section'] = current_section;
			
			// perform the AJAX request.
			$.ajax({
				type: 'GET',
				url: ajaxurl,
				data: data,
				dataType: 'json'
			})
			.done(function( data )
			{
				if( !data || !data.status )
				{
					if( data.message ) alert( data.message );
					else alert( 'An error occurred while getting the post list.' );
					return;
				}

				var hidden_input_values = $(hidden_input).val().split(',');
				var previous_section = hidden_input_values[0];
				var previous_items = hidden_input_values[1];

				previous_selected_items = [];
				if( previous_section == current_section )
				{
					var post_list_selects = $(post_list).find('select');
					for( var i = 0; i < post_list_selects.length; i++ )
					{
						previous_selected_items.push( $(post_list_selects[i]).find(':selected').val() );
					}
				}

				var current_items = parseInt( $(items_select).find(':selected').val() );

				$(post_list).html('');


				for( var i = 0; i < current_items; i++ )
				{
					var selected_item_id = -1;
					if( previous_selected_items.length > i )
						selected_item_id = parseInt(previous_selected_items[i]);

					var html = '<p>';
					html += '<select name="'+name+'[posts]['+i+']" class="widefat">';

					html += '<option value="-1" ';
					if( selected_item_id == -1 )
						html += 'selected="selected" ';
					html += '>-- Latest Post --</option>';

					for( var j = 0; j < data.posts.length; j++ )
					{
						var post = data.posts[j];
						html += '<option value="'+post.ID+'" ';
						if( selected_item_id == post.ID )
							html += 'selected="selected" ';
						html += '>'+post.post_title+'</option>';
					}
					html += '</select>';
					html += '</p>';

					$(post_list).append( html );
				}

				$(hidden_input).val( current_section+','+current_items );
			})
			.fail(function( jqXHR, textStatus )
			{
				alert( "Failed to obtain section post list:\n" + jqXHR.responseText+': '+textStatus );
			});
		}

	}
	
})( jQuery );

