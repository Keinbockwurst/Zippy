<?php
class Spaceremover {
    public function removespaces() {
      $ordner = 'files/';
      $alledateien = array_diff(scandir($ordner), array('..', '.'));

      if (count($alledateien, COUNT_RECURSIVE) > 2) {
        foreach ($alledateien as $file) {
            $newnamewospaces = preg_replace("/\s+/", "_", $file);
            rename($ordner.$file , $ordner.$newnamewospaces);
        }
      }
    }
}
?>
