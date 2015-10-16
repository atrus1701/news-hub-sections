<?php global $nhs_section, $nhs_stories; ?>

<div class="section-box <?php echo $nhs_section->key; ?>-section <?php echo $nhs_section->thumbnail_image; ?>-image">

	<h2>
	<?php echo nhs_get_anchor( 
		$nhs_section->get_section_link(), 
		$nhs_section->name.' Archives', 
		null,
		$nhs_section->title ); ?>
	</h2>
	

	<?php foreach( $nhs_stories as $nhs_story ): ?>

		<?php extract($nhs_story->nhs_data); ?>

		<div <?php post_class( 'story '.$nhs_section->key.'-section '.$nhs_section->thumbnail_image.'-image' ); ?>>

			<?php echo nhs_get_anchor( $link, $title ); ?>

			<?php if( $nhs_section->thumbnail_image !== 'none' ): ?>
				<?php if( $nhs_section->thumbnail_image !== 'none' ): ?>
					<div class="image" <?php if($image) echo 'style="background-image:url(\''.$image.'\')"'; ?> title="Featured Image">
						
						<?php if( $embed ): ?>
							<?php echo $embed; ?>
						<?php endif; ?>

					</div><!-- .image -->
				<?php endif; ?>
			<?php endif; ?>

			<div class="details">
			
				<h3><?php echo $title; ?></h3>
				
				<?php if( count($description) > 0 ): ?>

					<div class="description">

					<?php foreach( $description as $key => $value ): ?>
						<div class="<?php echo $key; ?>"><?php echo $value; ?></div>
					<?php endforeach; ?>
					
					</div><!-- .description -->

				<?php endif; ?>
			
			</div><!-- .details -->
			
			</a>

		</div><!-- .post -->

	<?php endforeach; ?>

	<div class="more">
		<?php echo nhs_get_anchor( 
			$nhs_section->get_section_link(), 
			$nhs_section->name.' Archives', 
			null,
			'More <em>'.$nhs_section->name.'</em> &raquo;' ); ?>
	</div><!-- .more -->

</div><!-- .section-box -->

