<style type="text/css">
#newmov .form-field input {
	width: 25em;
	}
</style>
<div class="wrap">    
    <h2><?php echo $title;?></h2>
    <div id="ajax-response"></div>
    <p>Add new movie.</p>
    <form action="" method="post" name="newmov" id="newmov" class="validate">
    	<input type="hidden" name="<?php echo $this->slug . '_controller'; ?>" value="<?php echo $_GET['page'];?>"/>
        <input type="hidden" name="<?php echo $this->slug . '_method'; ?>" value="add"/>		
		<table class="form-table">
			<tbody>
				<tr class="form-field form-required">
					<th scope="row"><label for="mov_title"><?php _e('Title'); ?> <span class="description"><?php _e('(required)'); ?></span></label></th>
					<td><input required name="mov_title" type="text" id="mov_title" value="<?php echo esc_attr($new_mov_title); ?>" aria-required="true" /></td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="mov_url"><?php _e('URL'); ?> <span class="description"><?php _e('(required)'); ?></span></label></th>
					<td><input required name="mov_url" type="url" id="mov_url" value="<?php echo esc_attr($new_user_mov_url); ?>" /></td>
				</tr>			
			</tbody>
		</table>


			<?php submit_button('Create','primary','create-btn') ?>
		</form>

</div>

