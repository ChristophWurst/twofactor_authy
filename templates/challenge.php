<?php
script('twofactor_authy', 'challenge');
?>

<form method="POST" id="authy-form">
	<input id="uuid" type="hidden" name="uuid" value="<?php p($_['uuid']) ?>">
	<input id="challenge" type="hidden" name="challenge" value="">
	<p><?php p($l->t('Please confirm the login on your smartphone.')) ?></p>
</form>
