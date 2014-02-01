<?php
	require_once('config.php');
	$monthnames = array('Jan' => '01', 'Feb' => '02');
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
			$dateyear = date('Y-');
			$datemonth = $monthnames[trim(preg_replace('/^([A-Z][a-z]+) .+/i', '$1', $line))];
			$dateday = trim(preg_replace('/^[a-zA\-Z]+ +([0-9]+) .+/i', '-$1', $line));
			$datetime = trim(preg_replace('/^[a-zA\-Z]+ +[0-9]+ ([0-9]+:[0-9]+:[0-9]+) .+/i', ' $1', $line));
			$date = trim($dateyear.$datemonth.$dateday.' '.$datetime);
			$type = trim(preg_replace('/(^.+postfix\/)([a-z]+)(.+$)/i', '$2', $line));
		    if ($type !== 'qmgr') {
		    	$num = trim(preg_replace('/(^.+postfix\/[a-z]+\[)([0-9]+)(\]: .+$)/i', '$2', $line));
		    } else {
		    	$num = '';
		    }
			$msg = preg_replace('/^.+postfix\/[a-z]+\[[0-9]+\]: /i', '', $line);
			$msg = ucfirst(trim(htmlentities(preg_replace('/^[A-Z0-9]+: /i', '', $msg))));
			echo trim($line)."\n".$date.' X '.md5($msg).' X '.$type.' X '.$num.' X '.$msg."\n\n\n";
			$duplicate = false;
			foreach ($db as $field) {
				if ($field['datetime'] == $date && md5($field['msg']) == md5($msg)) {
					$duplicate = true;
				}
			}
			//$duplicate = true;
			if ($duplicate == false) {
				if (!$mysqli->query("INSERT INTO `".$config['database']."`.`".$config['table']."` (`datetime`, `type`, `num`, `msg`) VALUES ('".$date."', '".$type."', '".$num."', '".$msg."');")) {
					printf("%s\n", $mysqli->error);
				}
			}
		}
	}
?>
