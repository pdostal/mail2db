<?php
	require_once('config.php');
	if ( !empty($config['server']) && !empty($config['username']) && !empty($config['password']) && !empty($config['database']) ) {
		$mysqli = new mysqli($config['server'], $config['username'], $config['password'], $config['database']);
	}
	$db = array();
	foreach ($mysqli->query('SELECT * FROM `'.$config['table'].'`') as $field) {
		$db[] = array('datetime' => $field['datetime'], 'msg' => $field['msg']);
		//echo $field['datetime'].' '.md5($field['msg']).' '.$field['msg']."\n";
	}
	$parse = file('/var/log/mail.log');
	foreach ($parse as $line) {
		if (preg_match('/postfix\/qmgr\[.+|postfix\/smtp\[.+/i', $line)) {
			$datetime = trim(preg_replace('/ [a-zA-Z0-9]+ postfix\/.+$/i', '', $line));
			$datetime = date('Y-m-').preg_replace('/^([A-Z][a-z]+) ([0-9]+) (.+)/i', '$2 $3', $datetime);
			$type = trim(preg_replace('/(^.+postfix\/)([a-z]+)(.+$)/i', '$2', $line));
		    if ($type !== 'qmgr') {
		    	$num = trim(preg_replace('/(^.+postfix\/[a-z]+\[)([0-9]+)(\]: .+$)/i', '$2', $line));
		    } else {
		    	$num = '';
		    }
			$msg = preg_replace('/^.+postfix\/[a-z]+\[[0-9]+\]: /i', '', $line);
			$msg = ucfirst(trim(htmlentities(preg_replace('/^[A-Z0-9]+: /i', '', $msg))));
			//echo trim($line)."\n".$datetime.' X '.md5($msg).' X '.$type.' X '.$num.' X '.$msg."\n\n\n";
			$duplicate = false;
			foreach ($db as $field) {
				if ($field['datetime'] == $datetime && md5($field['msg']) == md5($msg)) {
					$duplicate = true;
				}
			}
			if ($duplicate == false) {
				if (!$mysqli->query("INSERT INTO `".$config['database']."`.`".$config['table']."` (`datetime`, `type`, `num`, `msg`) VALUES ('".$datetime."', '".$type."', '".$num."', '".$msg."');")) {
					printf("%s\n", $mysqli->error);
				}
			}
		}
	}
?>