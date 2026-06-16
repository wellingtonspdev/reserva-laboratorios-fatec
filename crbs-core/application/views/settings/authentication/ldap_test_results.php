<div id="ldap_test_results" class="cps-admin-test-results">

<?php
if ( ! empty($errors)) {
	foreach ($errors as $err) {
		$key = sprintf('auth.ldap.error.%s', $err);
		$err_msg = $this->lang->line($key, false);
		if ($err_msg === false) {
			$err_msg = $err;
		}
		echo "<div class='cps-admin-test-result cps-admin-test-result-error'>" . html_escape($err_msg) . "</div>";
	}

	echo "<div class='cps-admin-test-result'>";
	echo "<div class='font-bold'>" . lang('auth.ldap.test.bind_dn') . "</div>";
	echo "<code>" . html_escape($user_bind_dn) . "</code>";
	if ( ! empty($config['search_filter'])) {
		echo "<div class='mt-3 font-bold'>" . lang('auth.ldap.test.search_filter') . "</div>";
		echo "<code>" . html_escape($user_search_filter) . "</code>";
	}
	echo "</div>";
}

if ($user === TRUE || is_array($user)) {
	echo "<div class='cps-admin-test-result cps-admin-test-result-success'>" . lang('auth.ldap.test.auth_success') . "</div>";

	if (is_array($mapping)) {
		$field_labels = [
			'firstname' => lang('user.field.firstname'),
			'lastname' => lang('user.field.lastname'),
			'displayname' => lang('user.field.displayname'),
			'email' => lang('user.field.email'),
		];

		echo "<div class='cps-admin-test-result'><dl class='space-y-2 text-sm'>";
		foreach ($mapping as $field => $value) {
			$label = $field_labels[$field];
			if ($value === FALSE) {
				$value = sprintf('<em>(%s)</em>', strtolower(lang('app.skipped')));
			}
			echo "<div><dt class='font-bold text-cps-gray-text'>{$label}</dt>";
			echo "<dd>{$value}</dd></div>";
		}
		echo "</dl></div>";
	}
}
?>

</div>
