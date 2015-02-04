<?php

/*
 *  Interactive Marvel Heroes Forum Signature Generator
 *
  Copyright 2013 Sean McGinnis
  http://www.seanwmcginnis.com/marvelheroes/loadcharacters.php

  Permission is hereby granted, free of charge, to any person obtaining
  a copy of this software and associated documentation files (the
  "Software"), to deal in the Software without restriction, including
  without limitation the rights to use, copy, modify, merge, publish,
  distribute, sublicense, and/or sell copies of the Software, and to
  permit persons to whom the Software is furnished to do so, subject to
  the following conditions:

  The above copyright notice and this permission notice shall be
  included in all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
  MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
  NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
  LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
  OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
  WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

include 'marvelheroes_config.php';
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, "marvelheroesdb");
$ret = array();
$query = "SELECT * FROM character_images ORDER BY display_position, costume_index";
$result = $mysqli->query($query);

$last_char = "";
$last_char_index = "";

// This puts the heroes in an array -- old hat by now.
while ($myrow = $result->fetch_assoc()) {
    $char = NULL;
    $char_index = strval($myrow['character_index']);
    $found = false;
    for ($i = 0; $i < count($ret); $i++) {
        if (strcmp($ret[$i]['character_index'], $char_index) == 0) {
            $found = true;
            $ret[$i]['character_index'] = strval($myrow['character_index']);
            $ret[$i]['character_name'] = $myrow['character_name'];
            array_push($ret[$i]['costume_index'], strval($myrow['costume_index']));
            array_push($ret[$i]['image_file'], "images_new/" . $myrow['image_file']);
            array_push($ret[$i]['costume_name'], $myrow['costume_name']);
            break;
        }
    }
    if (!$found) {
        $char = array();
        $char['character_index'] = strval($myrow['character_index']);
        $char['character_name'] = $myrow['character_name'];
        $char['costume_index'] = array();
        array_push($char['costume_index'], strval($myrow['costume_index']));
        $char['image_file'] = array();
        array_push($char['image_file'], "images_new/" . $myrow['image_file']);
        $char['costume_name'] = array();
        array_push($char['costume_name'], $myrow['costume_name']);
        array_push($ret, $char);
    }
}
$result->close();
$mysqli->close();
echo json_encode($ret);
?>