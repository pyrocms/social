<?php echo form_open('admin/social/token_save') ?>

	<div class="form_inputs">

		<fieldset>

			<p><?php echo lang('social:pick_account_message'); ?></p>
			<ul>
				<li>
					<label for="account"><?php echo lang('social:pick_account'); ?> <span>*</span></label>
					<div class="input"><?php echo form_dropdown('account', array('main' => $main['name']) + $accounts); ?></div>		
				</li>
			<ul>
			
		</fieldset>

	</div>
	
	<div class="buttons">
		<?php $this->load->view('admin/partials/buttons', array('buttons' => array('save'))); ?>
	</div>	
		
<?php echo form_close() ?>