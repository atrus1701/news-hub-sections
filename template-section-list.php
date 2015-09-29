<?php global $nhs_section, $nhs_stories; ?>

<div class="section-box <?php echo $nhs_section->name; ?>-section <?php echo $nhs_section->thumbnail_image; ?>-image">

	<h2>
	<?php echo nhs_get_anchor( 
		$nhs_section->get_section_link(), 
		$nhs_section->name.' Archives', 
		null,
		$nhs_section->title ); ?>
	</h2>
	
	<?php foreach( $nhs_stories as $story ): ?>

		<?php extract($story->nhs_data); ?>

		<div class="post featured-post">

			<?php echo nhs_get_anchor( $link, $title ); ?>

			<?php if( $section->thumbnail_image !== 'none' ): ?>
				<div class="image">
					
					<?php if( $image ): ?>
						<img src="<?php echo $image; ?>" alt="Featured Image" />
					<?php endif; ?>
					
					<?php if( $embed ): ?>
						<?php echo $embed; ?>
					<?php endif; ?>

				</div><!-- .image -->
			<?php endif; ?>

			<div class="details">
			
				<h3><?php echo $title; ?></h3>
				
				<?php if( count($description) > 0 ): ?>

					<div class="description">

					<?php 
					foreach( $description as $key => $value ):
						if( is_array($value) ):
							
							?><div class="<?php echo $key; ?>"><?php
							
							foreach( $value as $k => $v ):
								?><div class="<?php echo $k; ?>"><?php echo $v; ?></div><?php
							endforeach;
						
							?></div><?php
							
						else:

							?><div class="<?php echo $key; ?>"><?php echo $value; ?></div><?php
							
						endif;
					endforeach;
					?>
			
					</div><!-- .description -->

				<?php endif; ?>
			
			</div><!-- .details -->
			
			</a>

		</div><!-- .post.featured-post -->

	<?php endforeach; ?>

	<div class="more">
		<?php echo nhs_get_anchor( 
			$nhs_section->get_section_link(), 
			$nhs_section->name.' Archives', 
			null,
			'More <em>'.$nhs_section->name.'</em> &raquo;' ); ?>
	</div><!-- .more -->

</div><!-- .section-box -->

