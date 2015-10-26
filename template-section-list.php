<?php global $nhs_section, $nhs_stories; ?>

<div class="section-box <?php echo $nhs_section->key; ?>-section <?php echo $nhs_section->thumbnail_image; ?>-image">

	<h2>
		<a href="<?php echo $nhs_section->get_section_link(); ?>" title="<?php echo $nhs_section->title; ?> Archives"><?php echo $nhs_section->title; ?></a>
	</h2>
	

	<?php foreach( $nhs_stories as $nhs_story ): ?>

		<?php extract($nhs_story->nhs_data); ?>

		<div <?php post_class( 'story '.$nhs_section->key.'-section '.$nhs_section->thumbnail_image.'-image listing' ); ?>>
			
			<?php
			switch( $nhs_section->thumbnail_image ):
				case 'landscape':
				case 'portrait':
					?><div class="image" <?php if($image) echo 'style="background-image:url(\''.$image.'\')"'; ?> title="Featured Image"></div><!-- .image --><?php
					break;
				case 'normal':
					if( $image ):
						?><div class="image" title="Featured Image"><img src="<?php if($image) echo $image; ?>" /></div><!-- .image --><?php
					endif;
					break;
				case 'embed':
					?><div class="image"><?php if( $embed ) echo $embed; ?></div><!-- .image --><?php
					break;
				default:
					break;
			endswitch;
			?>

			<div class="details">
			
				<h3><?php echo vtt_get_anchor( $link, $title, null, $title ); ?></h3>
				<?php if( isset($byline) ): ?>
					<div class="byline"><?php echo $byline; ?></div>
				<?php endif; ?>
				
				<?php if( count($description) > 0 ): ?>

					<div class="description">

					<?php foreach( $description as $key => $value ): ?>
						<div class="<?php echo $key; ?>"><?php echo $value; ?></div>
					<?php endforeach; ?>
					
					</div><!-- .description -->

				<?php endif; ?>
			
			</div><!-- .details -->
			
		</div><!-- .post -->

	<?php endforeach; ?>

	<div class="more">
		<?php echo vtt_get_anchor( 
			$nhs_section->get_section_link(), 
			$nhs_section->name.' Archives', 
			null,
			'More <em>'.$nhs_section->name.'</em> &raquo;' ); ?>
	</div><!-- .more -->

</div><!-- .section-box -->

