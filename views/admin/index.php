<script>
jQuery(function($) {
	
	$('form.save_credentials')
		.submit(function(){
			var $form = $(this);
			var $save = $('button.save', $form);
			var $status = $('button.status', $form);
		
			$form.ajaxError(function(e, jqxhr, settings, exception) {
				alert('Failed to save.');
			});
		
			$.post($form.attr('action'), $form.serialize(), function(response, status) {
				$save.prop('disabled', true);
				$status.prop('disabled', false);
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
	$('div.provider').each(function() {
		$provider = $(this);
		
		// If they have stuff then show
		if ($('input[name=client_key]', $provider).val() && $('input[name=client_secret]', $provider).val()) {
			$('button.token', $provider).prop('disabled', false);
			$('button.status', $provider).prop('disabled', false).removeProp('disabled');
			return;
		}
		
		// Otherwise disable that token button!
		$('button.token', $provider).prop('disabled', true);
		$('button.status', $provider).prop('disabled', true);
	});	
	
	$('button.clear').click(function() {
		var provider = $(this).closest('div.provider').data('provider');
		$.post(SITE_URL + 'admin/social/remove_credentials', { provider: provider }, function() {
			window.location.href = window.location.href;
		});
	});
	
	$('button.status').click(function() {
		
		var $provider = $(this).closest('div.provider'),
			provider = $provider.data('provider'),
			status = this.value;
		
		$.post(SITE_URL + 'admin/social/save_status/' + provider, { status: status }, function() {
			if (parseInt(status) === 1) {
				$('button[name=enable]', $provider).hide();
				$('button[name=disable]', $provider).removeClass('hidden').show();
			}
			else {
				$('button[name=enable]', $provider).removeClass('hidden').show();
				$('button[name=disable]', $provider).hide();
			}
		});
	});
	
	$('button.token').click(function() {
		
		var provider = $(this).closest('div.provider').data('provider');
		var url = SITE_URL + 'admin/social/token_redirect/' + provider;
				
		auth_window = window.open(url, 'provider-auth','width=600,height=500');
		
		auth_window.onunload = function() {
			window.location.href = window.location.href;
		}
	});
	
	
	$('div.tokens dd span').live('click', function() {
		$(this).parent().html( $('<input/>').val($(this).text()).css('width', '100%').prop('readonly', true) );
	});
	
	$('div.tokens dd input').live('blur', function() {
		$(this).parent().html( $('<span/>').text($(this).val()) );
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

		<div data-provider="<?php echo $provider ?>" class="provider one_half <?php echo empty($details['credentials']) ? 'no_credentials' : 'has_credentials' ?> <?php echo alternator('', 'last') ?>" style="width: 485px">

			<?php echo form_open('admin/social/save_credentials/'.$provider, 'class="save_credentials"') ?>
			
				<div class="form_inputs">

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
								<div class="input"><?php echo form_input('scope', isset($details['credentials']) ? $details['credentials']->scope : (empty($details['default_scope']) ? '' : $details['default_scope'])); ?></div>				
							</li>
							
						</ul>

						<div class="buttons">
							
							<button type="submit" name="save" value="save" class="btn blue save" disabled>
								<span><?php echo lang('buttons.save'); ?></span>
							</button>
							
							<button type="button" name="remove" value="<?php echo $provider ?>" class="btn red clear" <?php echo empty($details['credentials']->client_key) ? 'disabled' : '' ?>>
								<span><?php echo lang('global:remove'); ?></span>
							</button>
							
							<button type="button" name="disable" value="0" class="btn red status <?php echo empty($details['credentials']->is_active) ? 'hidden' : '' ?>">
								<span><?php echo version_compare(CMS_VERSION, '2.0.9', '>') ? lang('global:disable') : lang('disable_label') ?></span>
							</button>
							
							<button type="button" name="enable" value="1" class="btn green status <?php echo empty($details['credentials']->is_active) ? '' : 'hidden' ?>">
								<span><?php echo version_compare(CMS_VERSION, '2.0.9', '>') ? lang('global:enable') : lang('enable_label') ?></span>
							</button>

							<button type="button" class="btn orange token">
								<span><?php echo lang('social:get_tokens'); ?></span>
							</button>
							
						</div>
						
						<div class="tokens">
							<dl>
								<dt><?php echo lang('social:access_token') ?></dt>
								<dd><?php echo isset($details['credentials']->access_token) ? "<span>{$details['credentials']->access_token}</span>" : lang('global:check-none') ?></dt>
							
								<?php if ($details['strategy'] == 'oauth'): ?>
									
								<dt><?php echo lang('social:secret') ?></dt>
								<dd><?php echo ! empty($details['credentials']->secret) ? "<span>{$details['credentials']->secret}</span>" : lang('global:check-none') ?></dt>
									
								<dt><?php echo lang('social:refresh_token') ?></dt>
								<dd><em>n/a</em></dd>
								
								<dt><?php echo lang('social:expires') ?></dt>
								<dd><em>n/a</em></dd>
							
								<?php elseif ($details['strategy'] == 'oauth2'): ?>
						
								<dt><?php echo lang('social:secret') ?></dt>
								<dd><em>n/a</em></dd>
							
								<dt><?php echo lang('social:refresh_token') ?></dt>
								<dd><?php echo isset($details['credentials']->refresh_token) ? $details['credentials']->refresh_token : lang('global:check-none') ?></dt>
								
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