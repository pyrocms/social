<section class="title">
	<h4><?php echo lang('blog_posts_title'); ?></h4>
</section>

<script>
jQuery(function($) {
	
	$('form.save_credentials')
		.submit(function(){
			var $form = $(this);
			var $save = $('button', $form)
		
			$form.ajaxError(function(e, jqxhr, settings, exception) {
				alert('Failed to save.');
			});
		
			$.post($form.attr('action'), $form.serialize(), function(response, status) {
				$save.prop('disabled', true);
				$('span', $save).text('Saved!');
			}, 'json');
		
			return false;
		})
		.find('input[type=text]').keyup(function(event) {
			// skip enter
			if (event.which == 13) return;
			
			// change something? enable save button
			$(this).closest('form').find('button.save').removeProp('disabled');
		});
		
	// Disable all buttons, stop caching state
	$('form.save_credentials button.save').prop('disabled', true);
})
</script>

<style>
.form_inputs fieldset > ul > li > label {
	width: 24%;
}
</style>

<section class="item">

	<?php foreach ($providers as $provider => $details): ?>

		<div class="<?php echo empty($details['credentials']) ? 'no_credentials' : 'has_credentials' ?>">

			<?php echo form_open('admin/social/save_credentials/'.$provider, 'class="save_credentials"') ?>
			
				<div class="form_inputs one_half">

					<fieldset>
<h5><?php echo $details['human'] ?></h5>
						<ul>
							<li>
								<label for="client_key"><?php echo lang('social:client_key'); ?> <span>*</span></label>
								<div class="input"><?php echo form_input('client_key', isset($details['credentials']) ? $details['credentials']->client_key : ''); ?></div>				
							</li>
							
							<li>
								<label for="client_secret"><?php echo lang('social:client_secret'); ?> <span>*</span></label>
								<div class="input"><?php echo form_input('client_secret', isset($details['credentials']) ? $details['credentials']->client_secret : ''); ?></div>				
							</li>
							
							<li>
								<label for="scope"><?php echo lang('social:scope'); ?></label>
								<div class="input"><?php echo form_input('scope', isset($details['credentials']) ? $details['credentials']->scope : ''); ?></div>				
							</li>
							
						</ul>

						<div class="buttons">
							
							<button type="submit" name="btnAction" value="save" class="btn blue save" disabled>
								<span><?php echo lang('buttons.save'); ?></span>
							</button>
							
						</div>
					
					</fieldset>

				</div>
				
			<?php echo form_close() ?>
			
		</div>

	<?php endforeach; ?>

</section>