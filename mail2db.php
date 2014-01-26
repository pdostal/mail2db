<?php
	$parse = file('/var/log/mail.log');
	foreach ($parse as $line) {
		if (preg_match('/postfix\/[qmgr|smtp]/', $line)) {
			$date = trim(preg_replace('/ [a-zA-Z0-9]+ postfix\/.+$/', '', $line));
			$msg = trim(preg_replace('/^.+postfix\//', '', $line));
			echo $date.' '.md5($msg).' '.$msg."\n";
		}
	}
?>