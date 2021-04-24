<?php

// Get the config file
require '/var/www/VSW-GUI-CONFIG';

session_start();

// Verify user then check $_GET values for issued server commands
if (isset($_SESSION['login']) && $_SESSION['login'] == $hash) {
  if (isset($_GET['start'])) {
    $info = exec('sudo systemctl start valheimserver.service');
    header("Location: $_SERVER[PHP_SELF]");
    exit;
  }
  if (isset($_GET['stop'])) {
    $info = exec('sudo systemctl stop valheimserver.service');
    header("Location: $_SERVER[PHP_SELF]");
    exit;
  }
  if (isset($_GET['restart'])) {
    $info = exec('sudo systemctl restart valheimserver.service');
    header("Location: $_SERVER[PHP_SELF]");
    exit;
  }
  if (isset($_GET['seed'])) {
    $command = exec('sudo cp -R /home/steam/.config/unity3d/IronGate/Valheim/worlds/* /var/www/html/download/');
    header("Location: $_SERVER[PHP_SELF]");
    exit;
  }
  if (isset($_GET['download_db'])) {
    $command = exec('sudo cp -R /home/steam/.config/unity3d/IronGate/Valheim/worlds/* /var/www/html/download/');
    $dir    = '/var/www/html/download/';
    $files = scandir($dir);
    foreach ($files as $key => $value) {
      $ext  = (new SplFileInfo($value))->getExtension();
      if ($ext == 'db' ) {
        header('location: /download/'.$value);
        exit;
      }
    }
    trigger_error('No .db file found, check permissions and try again.');
    exit;
  }
  if (isset($_GET['download_fwl'])) {
    $command = exec('sudo cp -R /home/steam/.config/unity3d/IronGate/Valheim/worlds/* /var/www/html/download/');
    $dir    = '/var/www/html/download/';
    $files = scandir($dir);
    foreach ($files as $key => $value) {
      $ext  = (new SplFileInfo($value))->getExtension();
      if ($ext == 'fwl' ) {
        header('location: /download/'.$value);
        exit;
      }
    }
    trigger_error('No .db file found, check permissions and try again.');
    exit;
  }
}

// Get the status of valheimserver.service
$info = shell_exec('systemctl status --no-pager -l valheimserver.service');
$plugin_config_files = shell_exec("ls /home/steam/valheimserver/BepInEx/config/");

// Pull all the values out of $info into more useful variables
$startup_line = strstr($info, '-name');
$startup_array = explode(' ', $startup_line);
$name = 'ERROR';
$world = 'ERROR';
$port = 'ERROR';
$public = 'ERROR';
foreach ($startup_array as $key => $value) {
  $next_key = $key + 1;
  switch ($value) {
    case '-name':
      $name = $startup_array[$next_key];
      break;
    case '-world':
      $world = $startup_array[$next_key];
      break;
    case '-port':
      $port = $startup_array[$next_key];
      break;
    case '-public':
      $public = $startup_array[$next_key];
      break;
    default:
      # Do nothing
      break;
  }
}
$world_perm = $world;
switch ($public) {
  case 0:
    $public_status = "Not Public";
    $public_class = "warning";
    break;
  case 1:
    $public_status = "Public";
    $public_class = "success";
  default:
    $public_status = "Error fetching data";
    $public_class = "danger";
    break;
};
$active = strstr($info, 'Active:');
$active = str_replace("Active: ", "", substr($active, 0, strpos($active, ";")));
$needle = "(dead)";
$pos = strpos($info, $needle);
// set values for some variable CSS
if ($pos > 0) {
  $alert_class = "danger";
  $world = "<span class='glyphicon glyphicon-remove red'></span>";
  $port = "<span class='glyphicon glyphicon-remove red'></span>";
  $public = "NONE";
  $name = "Valheim Service Not Running";
  $public_status = "<span class='glyphicon glyphicon-remove red'></span>";
  $public_class = "danger";
  $public_attr = "disabled";
  $no_download = '';
  $no_download_class = 'success';
  $url_copy = 'hidden';
  $start_attr = '';
} else {
  $alert_class = "success";
  $public_attr = "";
  $no_download = "disabled data-toggle=\"tooltip\" data-placement=\"top\" title=\"Must Stop Server to Download\"";
  $no_download_class = "danger";
  $url_copy = '';
  $start_attr = 'disabled';
}

// If the FWL has been copied to var/www/html/download run it through a hexdump and then clean the ASCII output to something legible
if (file_exists("/var/www/html/download/".$world.".fwl")) {
  $raw_fwl = shell_exec("hexdump -C /var/www/html/download/".$world.".fwl");
  $tempy = preg_match_all("/\|(.*)\|/siU", $raw_fwl, $hexdata_matches);
  $seed = $hexdata_matches[0][0] . $hexdata_matches[0][1];
  $seed = str_replace('.', ' ', $seed);
  $seed = str_replace('|', '', $seed);
  $seed_array = explode(' ', $seed);
  foreach ($seed_array as $key => $value) {
    if (!empty($value)) {
      $seed_output_array[] = $value;
    }
  }
  $seed = $seed_output_array[2];
  $has_seed = true;
  if ($make_seed_public == true) {
    $text_row = '7';
  } else {
    $text_row = '8';
  }
} else {
  $seed = "<button class=\"btn btn-xs btn-success\" onclick=\"location.href='index.php?seed=true';\">Get Seed</button>";
  $has_seed = false;
  $text_row = '8';
}

// ********** USER LOGOUT  ********** //
if(isset($_GET['logout'])) {
  unset($_SESSION['login']);
  unset($_SESSION['pheditor_admin']);
  header("Location: $_SERVER[PHP_SELF]");
  exit;
}

// ********** Form has been submitted ********** //
      if (isset($_POST['submit'])) {
        if ($_POST['username'] == $username && $_POST['password'] == $password){
          // If username and password correct, log in
          $_SESSION["login"] = $hash;
          header("Location: $_SERVER[PHP_SELF]");    
        } else {      
          // Display error on bad login
          display_login_form();
          echo '<div class="alert alert-danger">Incorrect login information.</div>';
          exit;
        }
      }
?>
<html>
  <head>
    <!-- JQuery and Bootstrap libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.7/themes/default/style.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/codemirror.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/addon/lint/lint.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/addon/dialog/dialog.min.css">
    <link rel="stylesheet" href="custom.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
</head>
<body style="background-color: #222; padding: 2vw;">
  <div class="wrapper">
    <!-- Start Please Wait Popover -->
    <div id="loading-background" class="hidden">
      <div id="loading-body" class="panel panel-default">
        <div class="spinner-grow text-primary" role="status">
          <span class="sr-only">Loading...</span>
        </div>
          Server Executing Command, Please wait.
      </div>
    </div>
    <!-- End Please Wait Popover -->
    <!-- Start Server information display -->
    <div class="row alert alert-<?php echo $alert_class; ?>" role="alert">
      <div class="col-<?php echo $text_row;?> h6">
        <span class="glyphicon glyphicon-hdd" aria-hidden="true"></span> <?php echo $active; ?>
      </div>
      <div class="col-4 <?php echo $url_copy;?>">
        <button id="copyButton" title="Click to copy" class="btn input-group-addon btn-<?php echo $alert_class;?>" <?php echo $alert_attr;?>><span class="glyphicon glyphicon-copy"></span></button>
        <input type="text" id="copyTarget" class="form-control" value="<?php echo $realIP . ':' . $port;?>">
      </div>
      <?php
        if ($make_seed_public == true && $has_seed == true ) { ?>
          <div class="col-1">
            <button class="btn btn-success view-world" onclick="window.open('https://valheim-map.world/?seed=<?php echo $seed; ?>&offset=506%2C778&zoom=0.077&view=0&ver=0.148.6')"><span class="glyphicon glyphicon-globe"></span></button>
          </div>
      <?php } ?>
    </div>
    <!-- End Server information display -->
    <?php
    if (isset($_SESSION['login']) && $_SESSION['login'] == $hash) {
    // *************************************** //
    // ********** Logged In Content ********** //
    // *************************************** //

    // Version Control
    $url = "https://raw.githubusercontent.com/Peabo83/Valheim-server-web-GUI-Simple/main/.gitignore/version";
    $latest_version = file_get_contents($url);
    $latest_version = strtok($latest_version, "\n");
    if ($version == $latest_version) {
      // DO NOTHING
    } else {
      echo "<div class='row alert alert-danger' role='alert'><div class='col-12'><span class='glyphicon glyphicon-warning-sign'></span> Your version of this GUI is out out of date. (current version: ".$version." - latest version:<a href='https://github.com/Peabo83/Valheim-Server-Web-GUI'>".$latest_version."</a>)</div></div>";
    }
    // End Version Control
    ?>
  <!-- Start Admin Panel -->
  <div class="panel panel-primary">
    <div class="panel-heading" role="tab" id="headingTwo">
      <h4 class="panel-title">
        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
          <?php echo $name; ?>
        </a>
      </h4>
    </div>
    <div id="collapseTwo" class="panel-collapse <?php echo $server_accordion;?>" role="tabpanel" aria-labelledby="headingTwo">
      <div class="panel-body">
        <label class="label label-info">Port</label> <?php echo $port; ?>
        <label class="label label-info">World</label> <?php echo $world; ?>
        <label class="label label-info">Public</label> <?php echo $public_status; ?>
        <label class="label label-info">Seed</label> <?php echo $seed; ?><br><br>
        <button class="btn btn-danger server-function" onclick="location.href='index.php?stop=true';" <?php echo $public_attr;?>>Stop</button> 
        <button class="btn btn-success server-function" onclick="location.href='index.php?start=true';" <?php echo $start_attr;?>>Start</button> 
        <button class="btn btn-warning server-function" onclick="location.href='index.php?restart=true';" <?php echo $public_attr;?>>Restart</button> 
        <button class="btn btn-<?php echo $no_download_class;?>" <?php echo $no_download; ?> onclick="location.href='index.php?download_db=true';">Download DB</button> 
        <button class="btn btn-<?php echo $no_download_class;?>" onclick="location.href='index.php?download_fwl=true';" <?php echo $no_download; ?>>Download FWL</button> <a class="btn btn-primary" href="?logout=true">Logout</a>
        <?php if ($server_log == true) { ?>
          <div class="panel-group" id="accordion2" role="serverlog" aria-multiselectable="true">
            <div class="panel panel-default">
              <div class="panel-heading" role="tab" id="serverlogs">
                <h4 class="panel-title">
                  <a role="button" data-toggle="collapse" data-parent="#accordion2" href="#serverlogbody" aria-expanded="false" aria-controls="serverlogbody" class="">
                    Server Logs
                  </a>
                </h4>
              </div>
              <div id="serverlogbody" class="panel-collapse collapse" role="logpanel" aria-labelledby="serverlogs">
                <div class="panel-body">
                  <?php
                    $log = shell_exec('sudo grep "Got connection SteamID\|Closing socket\|has wrong password\|Got character ZDOID from\|World saved" /var/log/syslog');
                    $log_array = explode("\n", $log);
                    foreach ($log_array as $key => $value) {
                      echo $value . "<br>";
                  }?>
                </div>
              </div>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>
  </div>
<?php }
// ********** End IF  ********** //
// ********** Login Form  ********** //
  else {
    display_login_form();
  }
  function display_login_form() { ?>
    <form action="<?php echo $self; ?>" method='post'>
    <div class="row login">
          <div class="col-5"><input type="text" name="username" id="username" class="form-control"></div>
          <div class="col-5"><input type="password" name="password" id="password" class="form-control"></div>
          <div class="col-2"><input class="btn btn-success" type="submit" name="submit" value="submit" style="width: 100%;"></div>
          <div style="display: none;">
          <textarea id="editor" data-file="" class="form-control"></textarea>
          <input id="digest" type="hidden" readonly>
          </div>
        </form>
    </div>
  <?php } ?>
</body>
</html>
