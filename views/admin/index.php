<script>
jQuery(function($) {
	
	$('form.save_credentials')
		.submit(function(){
			var $form = $(this);
			var $save = $('button.save', $form);
		
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
			
			var $form = $(this).closest('form');
			var $token = $('button.token', $form);
			var $save = $('button.save', $form);
			
			// change something? enable save button
			$save.prop('disabled', false).find('span').text('Save');
			
			if ($('input[name=client_key]', $form).val() && $('input[name=client_secret]', $form).val()) {
				$token.prop('disabled', false);
			}
			else {
				$token.prop('disabled', true);
			}
		});
		
	// Disable all save buttons, stop caching state
	$('form.save_credentials button.save').prop('disabled', true);
	
	// Disable all token buttons, if credentials are not there
	$('form.save_credentials button.token').each(function() {
		$form = $(this).closest('form');
		
		// If they have stuff then show
		if ($('input[name=client_key]', $form).val() && $('input[name=client_secret]', $form).val()) {
			$(this).prop('disabled', false);
			return;
		}
		
		// Otherwise disable that token button!
		$(this).prop('disabled', true);
	});
	
	$('button.clear').click(function() {
		$.post(SITE_URL + 'admin/social/remove_credentials', { provider: this.value }, function() {
			window.location.href = window.location.href;
		});
	});
	
	$('button.token').click(function() {
		
		var provider = this.value;
		var url = SITE_URL + 'admin/social/token_redirect/' + provider;
				
		auth_window = window.open(url, 'provider-auth','width=600,height=500');
		
		auth_window.onunload = function() {
			window.location.href = window.location.href;
		}
	});
	
})
</script>

<style>
.form_inputs fieldset > ul > li > label {
	width: 24%;
}

div.tokens dt {
	float: left;
	width: 100px;
}
div.tokens dd {
	float: left;
	width: 300px;
	text-overflow: ellipsis;
	overflow: hidden;
	white-space: nowrap;
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
							
							<button type="submit" name="save" value="save" class="btn blue save" disabled>
								<span><?php echo lang('buttons.save'); ?></span>
							</button>
							
							<button type="button" name="remove" value="<?php echo $provider ?>" class="btn red clear" <?php echo empty($details['credentials']->client_key) ? 'disabled' : '' ?>>
								<span><?php echo lang('global:remove'); ?></span>
							</button>

							<button type="button" value="<?php echo $provider ?>" class="btn orange token">
								<span><?php echo lang('social:get_tokens'); ?></span>
							</button>
							
						</div>
						
						<div class="tokens">
							<dl>
								<dt><?php echo lang('social:access_token') ?></dt>
								<dd><?php echo isset($details['credentials']->access_token) ? '1'.$details['credentials']->access_token : lang('global:check-none') ?></dt>
							
								<?php if ($details['strategy'] == 'oauth'): ?>
									
								<dt><?php echo lang('social:secret') ?></dt>
									<dd><?php echo ! empty($details['credentials']->secret) ? $details['credentials']->secret : lang('global:check-none') ?></dt>
										
									<dt><?php echo lang('social:refresh_token') ?></dt>
									<dd><em>n/a</em></dd>
									
									<dt><?php echo lang('social:expires') ?></dt>
									<dd><em>n/a</em></dd>
							
								<?php elseif ($details['strategy'] == 'oauth2'): ?>
							
									<dt><?php echo lang('social:secret') ?></dt>
									<dd><em>n/a</em></dd>
								
									<dt><?php echo lang('social:refresh_token') ?></dt>
									<dd><?php echo isset($details['credentials']->refresh_token) ? '1'.$details['credentials']->refresh_token : lang('global:check-none') ?></dt>
									
									<dt><?php echo lang('social:expires') ?></dt>
									<dd><?php echo ! empty($details['credentials']->expires) ? date('Y-m-d h:m:s', $details['credentials']->expires) : lang('global:check-none') ?></dt>
									
								<?php endif; ?>
							</dl>
						</div>
					
					</fieldset>

				</div>
				
			<?php echo form_close() ?>
			
		</div>

	<?php endforeach; ?>

</section>