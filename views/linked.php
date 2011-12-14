<h2><?php echo lang('social:linked_accounts'); ?></h2>

<ul>
<?php foreach ($authentications as $auth): ?>
	<li><?php echo ucfirst($auth->provider) ?> <span class="uid">(<?php echo $auth->uid ?>)</span></li>
<?php endforeach; ?>
</ul>