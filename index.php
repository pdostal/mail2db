<?php
  $startmicrotime = MicroTime(1);
  require_once('config.php');
  if ( !empty($config['server']) && !empty($config['username']) && !empty($config['password']) && !empty($config['database']) ) {
    $mysqli = new mysqli($config['server'], $config['username'], $config['password'], $config['database']);
  }
?>
<!doctype html>
<html lang="cs">
  <head>
    <meta charset="utf-8">
    <title>View</title>
    <meta name="description" content="View mail log" />
    <meta name="author" content="Pavel Dostál" />
    <meta name="robots" content="noindex, nofollow" />
    <!--[if lt IE 9]>
      <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <style>
      h1 { margin: 0px 0px; }

      header,footer { position: fixed; left: 0px; width: 98%; margin: 0px 1%; text-align: center; background-color: #fff; }

      header { height: 40px; top: 0px; padding-top: 5px; }
      footer { height: 20px; bottom: 0px; padding-bottom: 5px; }

      table { min-width: 300px; height: 100%; top: 0px; margin: 45px 5px 25px 5px; }

      .datetime { min-width: 105px; max-width: 105px; text-align: left; }
      .type { min-width: 35px; max-width: 35px; text-align: left; }
      .num { min-width: 45px; max-width: 45px; text-align: left; }
      .msg { min-width: 125px; overflow: visible; white-space: nowrap; text-align: left; }
    </style>
  </head>
  <body>
    <header>
      <h1>mail log</h1>
    </header>
    <table>
      <thead>
        <tr>
          <th class='datetime'>Date &amp; Time</th>
          <th class='type'>Type</th>
          <th class='num'>#</th>
          <th class='msg'>Message</th>
        </tr>
      </thead>
      <tbody>
<?php
  foreach ($mysqli->query('SELECT * FROM `'.$config['table'].'`') as $field) {
    $date = preg_replace('/^([0-9]+)-([0-9]+)-([0-9]+)( .+)/i', '$3.$2. $4', $field['datetime']);
    $type = preg_replace('/([a-z]+)(\[[0-9]+\]: .+)/i', '$1', $field['msg']);
    $num = preg_replace('/([a-z]+\[)([0-9]+)(\]: .+)/i', '$2', $field['msg']);
    if (preg_match('/^[a-z]+\[[0-9]+\]: [A-Z0-9]+: /i', htmlentities($field['msg']))) {
      $msg = ucfirst(preg_replace('/^[a-z]+\[[0-9]+\]: [A-Z0-9]+: /i', '', htmlentities($field['msg'])));
    } else {
      $msg = ucfirst(preg_replace('/^[a-z]+\[[0-9]+\]: /i', '', htmlentities($field['msg'])));
    }
    if (preg_match('/Removed$/', $msg)) {
      $class = 'removed';
    }
    echo "<tr class='".$class."'>\n";
    echo "<!-- ".htmlentities($field['msg'])." -->\n";
    echo "<td class='datetime'>".$date."</td>\n";
    echo "<td class='type'>".$type."</td>\n";
    echo "<td class='num'>".$num."</td>\n";
    echo "<td class='msg'>".$msg."</td>\n";
    echo "</tr>\n";
    unset($class,$date,$type,$num,$msg);
  }
?>
      </tbody>
    </table>
    <footer>
      <span><?php printf( "%01.2f sec", (MicroTime(1)-$startmicrotime) ); ?></span> |
      <span>&copy; <a itemprop="url" href="mailto:pdostal@pdostal.cz/">Pavel Dostál</a></span>
    </footer>
  </body>
</html>