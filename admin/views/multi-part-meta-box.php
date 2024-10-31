<p>
	<input type="checkbox" value="enabled" name="enable_multi_part" id="enable_multi_part" <?php echo !empty($enable_multi_part) ? 'checked' : ''; ?>>
	<label for="enable_multi_part"><?php _e( 'Enable Multi Part', $this->plugin_slug ); ?></label>
</p>

<div id="multi_part_configuration" <?php echo !empty($enable_multi_part) ? '' : 'style="display: none;"'; ?> data-inputs="1">
	<p><strong><?php _e('Note:',$this->plugin_slug); ?></strong> <?php _e('Unchecking "Enable Multi Part" will only remove <strong>this</strong> post from the grouping. The other posts will remain grouped. Removing only this post from the list below will have the same effect.',$this->plugin_slug); ?></p>
	<ol id="multi_part_list" data-main-post-id="<?php echo $post->ID; ?>">

		<?php if (empty($multi_part_json)) { ?>
			<li data-id="<?php echo $post->ID; ?>"><?php echo $post->post_title; ?> <strong>- This Post</strong> <a href="#" class="dashicons dashicons-no-alt multi-remove"></a></li>
		<?php } else {			
			foreach ($multi_part_posts as $each_multi_part_post) {
				echo '<li data-id="'.$each_multi_part_post->ID.'">'.$each_multi_part_post->post_title.($each_multi_part_post->ID == $post->ID ? ' <strong>- This Post</strong>' : '').' <a href="#" class="dashicons dashicons-no-alt multi-remove"></a></li>';
			}
		} ?>

	</ol>
	<input type="hidden" name="multi_part_data" id="multi_part_data" value="<?php echo $id_json; ?>">
	<p>
		<label for="multi_part_post_select"><?php _e('Group with:',$this->plugin_slug); ?></label><br>
		<select name="multi_part_post_select" id="multi_part_post_select">
			<?php
				foreach ($all_posts as $post) {
					setup_postdata($post);
					echo '<option data-group="'.get_post_meta($post->ID,'multi_part_data',true).'" data-title="'.$post->post_title.'" value="'.$post->ID.'"'.(in_array($post->ID, $exclude_ids) ? 'disabled' : '').'>';
						/**
						 * Might add this back in later...
						 */
						// the_time('Y/m/d'); 
						// echo ' - ';
						the_title();
					echo '</option>';
				}
				wp_reset_postdata();
			?>
		</select><br>
		<input type="button" class="button button-primary multi-add" id="multi-add" value="<?php _e
		('Add',$this->plugin_slug); ?>">
		<span id="multi_part_confirm">
			<span class="multi_part_confirm_name"></span>
			<?php _e('already belongs to a group. Do you want to merge?',$this->plugin_slug); ?>
			<a href="#" class="button button-primary yes"><?php _e('Yes',$this->plugin_slug); ?></a>
			<a href="#" class="button no"><?php _e('No',$this->plugin_slug); ?></a>
		</span>
	</p>
</div>