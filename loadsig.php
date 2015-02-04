<?php

/*
 *  Interactive Marvel Heroes Forum Signature Generator
 *
  Copyright 2013 Sean McGinnis
  http://www.seanwmcginnis.com/marvelheroes/loadsig.php

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
$keyword = "";
$ret = array();
if (isset($_GET['keyword'])) {
    $keyword = $_GET['keyword'];
} else {
    $ret['error'] = "You must enter a keyword to load a sig.";
    echo json_encode($ret);
    return;
}
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, "marvelheroesdb");
$query = "SELECT config, chars_per_row, view_mode, font, position_grid, level_grid, costume_grid, border_type, flair_grid, include_link, include_tooltips, border_color, version, half_grids FROM saved_config WHERE keyword=?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $keyword);
$stmt->execute();
$stmt->bind_result($db_config, $db_chars_per_row, $db_view_mode, $db_font_index, $db_position_grid, $db_level_grid, $db_costume_grid, $db_border_type, $db_flair_grid, $db_include_link, $db_include_tooltips, $db_border_color, $db_version, $db_half_grids);
$stmt->fetch();
$ret['position_grid'] = $db_position_grid;
$ret['costume_grid'] = $db_costume_grid;
$ret['level_grid'] = $db_level_grid;
$ret['flair_grid'] = $db_flair_grid;
$ret['include_link'] = $db_include_link;
$ret['include_tooltips'] = $db_include_tooltips;
$ret['font'] = $db_font_index;
$ret['view_mode'] = $db_view_mode;
$ret['version_tag'] = $db_version;
$ret['border_type'] = $db_border_type;
if ($db_half_grids != null) {
    $ret['half_grids'] = $db_half_grids;
}
if ($db_border_color != null) {
    $ret['border_color'] = $db_border_color;
}
$stmt->close();

$query = "SELECT character_index, link FROM saved_links WHERE keyword=? ORDER BY character_index";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $keyword);
$stmt->execute();
$stmt->bind_result($db_character_index, $db_link);
$char_index = 0;
$val = "";
while ($stmt->fetch()) {
    while ($char_index < $db_character_index) {
        $val = $val . "|";
        $char_index += 1;
    }
    $val = $val . $db_link . "|";
    $char_index = $db_character_index + 1;
}
$ret['link_grid'] = $val;
$stmt->close();
$mysqli->close();
echo json_encode($ret);
?>