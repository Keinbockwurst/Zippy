<?php
/**
 * The Unzipper extracts .zip or .rar archives and .gz files on webservers.
 * It's handy if you do not have shell access. E.g. if you want to upload a lot
 * of files (php framework or image collection) as an archive to save time.
 *
 * @author  Andreas Tasch, at[tec], attec.at; Stefan Boguth, Boguth.org
 * @license GNU GPL v3
 * @version 0.3.0
 */
define('VERSION', '0.3.0');
if (!file_exists('files/')) {
    mkdir('files/', 0755);
}

include 'resources/classes/spaceremover.php';
Spaceremover::removespaces();
include 'resources/classes/unzip.php';
include 'resources/classes/explorer.php';
$unzipper = new Unzipper();
session_start();
$_SESSION['status'] = array();
$_SESSION['status'] = array('info' => 'Zippy erfolgreich gestartet. Bereit für weitere Aufgaben.');
$postcont = null;

if (isset($_POST['dounzip'])) {
    $postcont = 'unzip';
}
if (isset($_POST['newname']) && isset($_POST['oldname'])) {
  $setexp = true;
  $ext = strval (pathinfo($_POST['oldname'], PATHINFO_EXTENSION));
  $oldname = strval ($_POST['newname']);
  $newname = ("files/".$oldname.".".$ext);
  rename($_POST['oldname'], $newname);
  $_SESSION['status'] = array('success' => 'Datei erfolgreich umbenannt.');
}
if (isset($_POST['dozip'])) {
    $postcont = 'zip';
}
if (isset($_POST['upload'])) {
    $postcont = 'upload';
}
if (isset($_POST['explorer'])) {
    $postcont = 'explorer';
} else {
    $setexp = false;
}
if (isset($_POST['delete'])) {
  $setexp = true;
  $_SESSION['status'] = array('success' => 'Dateien erfolgreich gelöscht.');
  foreach ($_POST['delete'] as $files) {
    unlink($files);
  }
}


switch ($postcont) {
    case 'upload':
        include 'resources/classes/uploader.php';
        Uploader::doUpload();
        break;
    case 'zip':
        include 'resources/classes/zipper.php';
        $zippath = !empty($_POST['zippath']) ? strip_tags($_POST['zippath']) : '.';
        // Resulting zipfile e.g. zipper--2016-07-23--11-55.zip
        $zipfile = 'files/zipper-'.date('Y-m-d--H-i').'.zip';
        Zipper::zipDir($zippath, $zipfile);
        break;
    case 'unzip':
        $archive = isset($_POST['zipfile']) ? strip_tags($_POST['zipfile']) : '';
        $destination = isset($_POST['extpath']) ? strip_tags($_POST['extpath']) : '';
        $unzipper->prepareExtraction($archive, $destination);
        break;
}


?>
<!DOCTYPE html>
<html>
<head>
  <title>Zippy</title>
  <style>
    body {
      font-family: 'Ubuntu', sans-serif;
      line-height: 150%;
      background-color: #cccccc;
    }
  </style>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="gray" />
  <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
  <link rel="stylesheet" href="resources/animate.css">
  <link rel="stylesheet" href="resources/style.css">
  <script
			  src="https://code.jquery.com/jquery-3.1.1.min.js"
			  integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
			  crossorigin="anonymous">
  </script>
  <script type="text/javascript" src="resources/app.js"></script>
  <link rel="icon" href="resources/gfx/favicon.ico" type="image/x-icon">
</head>
<body>
  <div class="animated fadeIn wrapper">
    <div class="innerwrapper">
      <p class="status status--<?php echo strtoupper(key($_SESSION['status'])); ?>">
        <b>Status:</b> <?php echo reset($_SESSION['status']); ?><br/>
      </p>
      <div id="logo" class="animated bounceIn">
        <img src="resources/gfx/logo.png" alt="Logo" />
      </div>

      <fieldset class="field">
        <h1>Datei-Explorer (alpha)</h1><div class="icon icon-up"></div>
          <div class="innercont animated fadeIn">
          <p class="info">Geöffneter Pfad: <?php echo __DIR__ ?>/files/</p>
        <?php
            Explorer::createExplorer();
         ?>
         </div>
        </fieldset>

      <form action="" method="POST">
        <fieldset class="field">
          <h1>Archiv Unzipper</h1><div class="icon"></div>
            <div class="innercont animated fadeIn hide">
              <label for="zipfile">Wählen Sie ein .rar, .zip oder .gz-Archiv das sie entpacken wollen:</label>
              <select name="zipfile" size="1" class="select">
                <?php foreach ($unzipper->zipfiles as $zip) {
                        echo "<option>$zip</option>";
                      }
                ?>
              </select>
                <?php if (count($unzipper->zipfiles) == 0) {
                        echo '<b>Keine Dateien zum entpacken vorhanden!</b>';
                      }
                ?>
              <p class="info">Die zu entpackenden Archive müssen sich in folgendem Pfad befinden: <?php echo __DIR__ ?>/files/</p>
              <label for="extpath">Pfad zum Entpacken (Optional):</label>
              <input type="text" name="extpath" class="form-field" placeholder="<?php echo __DIR__ ?>" />
              <p class="info">Den gewünschten Pfad ohne Slash am Anfang oder Ende eingeben (z.B. "meinPfad").<br> Wenn das Feld leergelassen wird dann wird das Archiv im selben Pfad entpackt.</p>
              <input type="submit" name="dounzip" class="submit" value="Entpacken"/>
            </div>
        </fieldset>

        <fieldset class="field">
          <h1>Archiv Zipper</h1><div class="icon"></div>
            <div class="innercont animated fadeIn hide">
              <label for="zippath">Pfad den Sie zippen wollen (Optional):</label>
              <input type="text" name="zippath" class="form-field" placeholder="z.B. files"/>
              <p class="info">Den gewünschten Pfad ohne Slash am Anfang oder Ende eingeben (z.B. "meinPfad").<br> Wenn das Feld leergelassen wird dann wird der aktuelle Pfad verwendet. <br>Die Datei befindet sich nach dem erstellen im Pfad <?php echo __DIR__ ?>/files/</p>
              <input type="submit" name="dozip" class="submit" value="Packen"/>
            </div>
          </fieldset>
          </form>
          <form action ="" method="POST" enctype="multipart/form-data">
            <fieldset class="field">
              <h1>Archiv-Uploader</h1><div class="icon"></div>
              <div class="innercont animated fadeIn hide">
                <label for="uploader">Hochzuladende Datei:</label>
                <input type="file" name="uploaded" class="form-field" id="fileinput" />
                <output id="list"></output>
                <p class="info">Der Uploader benötigt Schreibrechte im Verzeichnis! Die Dateien werden in den Pfad files verschoben.<br> <b>Der Uploader akzeptiert nur .rar, .zip und .gz-Dateien mit maximal <?php echo ("<span id='maxsize' value='" . ini_get('upload_max_filesize') . "'</span>" . ini_get('upload_max_filesize') . "B.") ?></b></p>
                <input type="submit" name="upload" class="submit" value="Hochladen" id="uploadclick"/>
            </div>
          </fieldset>
        </form>

      <p class="version">Unzipper Version: <?php echo VERSION; ?></p>
    </div>
  </div>
    <div id="renamediv" class="hide animated fadeIn">
      <form action="" method="POST">
        <fieldset class="field">
          <h2>Umbennenen</h2><div class="cancel"></div>
            <div class="innercont">
              <input class="oldname" type="hidden" name="oldname" value="" />
              <label for="newname">Neuer Name:</label>
              <input type="text" name="newname" class="form-field" placeholder="Neuen Namen hier eintragen"/>
              <input type="submit" name="rename" class="submit" value="Umbenennen"/>
              <span class='close'>Abbrechen</span>
            </div>
          </fieldset>
        </form>
    </div>
</body>
</html>
