<?php
	require_once('config.php');
	if ( !empty($config['server']) && !empty($config['username']) && !empty($config['password']) && !empty($config['database']) ) {
		$mysqli = new mysqli($config['server'], $config['username'], $config['password'], $config['database']);
	}
	foreach ($mysqli->query('SELECT * FROM `'.$config['table'].'`') as $field) {
		$db[] = array('datetime' => $field['datetime'], 'msg' => $field['msg']);
		//echo $field['datetime'].' '.md5($field['msg']).' '.$field['msg']."\n";
	}
	$parse = file('/var/log/mail.log');
	foreach ($parse as $line) {
		if (preg_match('/postfix\/qmgr\[.+|postfix\/smtp\[.+/i', $line)) {
			$datetime = trim(preg_replace('/ [a-zA-Z0-9]+ postfix\/.+$/i', '', $line));
			$datetime = date('Y-m-').preg_replace('/^([A-Z][a-z]+) ([0-9]+) (.+)/i', '$2 $3', $datetime);
			$msg = trim(preg_replace('/^.+postfix\//i', '', $line));
			//echo $datetime.' '.md5($msg).' '.$msg."\n";
			$duplicate = false;
			foreach ($db as $field) {
				if ($field['datetime'] == $datetime && md5($field['msg']) == md5($msg)) {
					$duplicate = true;
				}
			}
			if ($duplicate == false) {
				if (!$mysqli->query("INSERT INTO `".$config['database']."`.`".$config['table']."` (`datetime`, `msg`) VALUES ('".$datetime."', '".$msg."');")) {
					printf("%s\n", $mysqli->error);
				}
			}
		}
	}
?>