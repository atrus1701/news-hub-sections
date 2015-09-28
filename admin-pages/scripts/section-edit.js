


function getUnique(a)
{
	var b = [a[0]], i, j, tmp;

	for (i = 1; i < a.length; i++)
	{
		tmp = 1;
		for (j = 0; j < b.length; j++)
		{
			if (a[i] == b[j])
			{
				tmp = 0;
				break;
			}
		}
		if (tmp) { b.push(a[i]); }
	}

	return b;
}



jQuery(document).ready( function()
{

	jQuery('#post-type-selection').each( function()
	{
		var self = this;
		
		jQuery(self).find('.post-type input[type=checkbox]')
			.change( function()
			{
				// compile list of taxonomies
				var pt = jQuery(self).find('.post-type');
				var taxonomies = [];
											
				for( var i = 0; i < pt.length; i++ )
				{
					if( jQuery(pt[i]).find('input[type=checkbox]').is(":checked") )
					{
						var taxs = jQuery(pt[i]).find('input[type=hidden]').val();
						taxs = taxs.split(',');
						for( var j = 0; j < taxs.length; j++ )
						{
							if( taxs[j] !== '' ) taxonomies.push( taxs[j] );
						}
					}
				}
				
				jQuery('#taxonomy-selection input[type=checkbox]').change();
				if( taxonomies.length == 0 )
				{
					jQuery('#no-taxonomies').show();
					jQuery('#taxonomy-selection').find('.taxonomy')
						.each( function()
						{
							jQuery(this).hide();
							jQuery('#taxonomies-selection .taxonomy').hide();
						});
				}
				else
				{
					taxonomies = getUnique( taxonomies );
					jQuery('#no-taxonomies').hide();

					jQuery('#taxonomy-selection').find('.taxonomy')
						.each( function()
						{
							var taxname = jQuery(this).attr('class').substring(9);
							if( taxonomies.indexOf( taxname ) < 0 )
							{
								jQuery(this).hide();
								jQuery('#taxonomies-selection .taxonomy.'+taxname).hide();
							}
							else
							{
								jQuery(this).show();
							}
						});
				}
			})
			.change();
	});

	jQuery('#taxonomy-selection input[type=checkbox]')
		.change( function()
		{
			var taxname = this.value;
			if( this.checked )
			{
				jQuery('#taxonomies-selection .taxonomy.'+taxname).show();
			}
			else
			{
				jQuery('#taxonomies-selection .taxonomy.'+taxname).hide();
			}
		})
		.change();

});

