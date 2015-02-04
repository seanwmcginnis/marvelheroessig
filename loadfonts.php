<?php

/*
 *  Interactive Marvel Heroes Forum Signature Generator
 *
  Copyright 2013 Sean McGinnis
  http://www.seanwmcginnis.com/marvelheroes/loadfonts.php

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
$query = "SELECT * FROM fonts ORDER BY font_name";
$result = $mysqli->query($query);
while ($myrow = $result->fetch_assoc()) {
    $font_index = intval($myrow['font_index']);
    $font_name = $myrow['font_name'];
    $font = array();
    $font['index'] = $font_index;
    $font['name'] = $font_name;
    array_push($ret, $font);
}
$result->close();
echo json_encode($ret);
?>