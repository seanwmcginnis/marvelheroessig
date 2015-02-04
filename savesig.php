<?php

/*
 *  Interactive Marvel Heroes Forum Signature Generator
 *
  Copyright 2013 Sean McGinnis
  http://www.seanwmcginnis.com/marvelheroes/savesig.php

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
$ret = array();
try {
    

$keyword = "";
if (isset($_GET['keyword'])) {
    $keyword = $_GET['keyword'];
} else {
    return;
}
$password = "";
if (isset($_GET['password'])) {
    $password = $_GET['password'];
} else {
    return;
}
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, "marvelheroesdb");

$position_grid = "";
if (isset($_GET['position_grid'])) {
    $position_grid = $_GET['position_grid'];
}

$costume_grid = "";
if (isset($_GET['costume_grid'])) {
    $costume_grid = $_GET['costume_grid'];
}

$half_grids = "";
if (isset($_GET['half_grids'])) {
    $half_grids = $_GET['half_grids'];
}

$level_grid = "";
if (isset($_GET['level_grid'])) {
    $level_grid = $_GET['level_grid'];
}

$flair_grid = "";
if (isset($_GET['flair_grid'])) {
    $flair_grid = $_GET['flair_grid'];
}

$include_link = "";
if (isset($_GET['include_link'])) {
    $include_link = $_GET['include_link'];
}

$include_tooltips = "";
if (isset($_GET['include_tooltips'])) {
    $include_tooltips = $_GET['include_tooltips'];
}

$link_grid = "";
if (isset($_GET['link_grid'])) {
    $link_grid = htmlspecialchars($_GET['link_grid']);
}

$version = 2;

$view_mode = 0;
$font = 0;
$border_type = 0;
$border_color = "00AAFF";

if (isset($_GET['view_mode'])) {
    $view_mode = intval($_GET['view_mode']);
}
if (isset($_GET['font'])) {
    $font = intval($_GET['font']);
}
if (isset($_GET['border_type'])) {
    $border_type = intval($_GET['border_type']);
}
if (isset($_GET['border_color'])) {
    $border_color = $_GET['border_color'];
}

// Here we do password checking
if (empty($keyword) || strlen($keyword) <= 0) {
    $ret['message'] =  "ERROR: No keyword submitted.";
    $mysqli->close();
    echo json_encode($ret);
    return;
}
if (empty($password) || strlen($password) <= 0) {
    $ret['message'] =  "ERROR: No password submitted.";
    $mysqli->close();
    echo json_encode($ret);
    return;
}
if (strstr($keyword, " ")) {
    $ret['message'] =  "ERROR: Keywords can not contain spaces.";
    $mysqli->close();
    echo json_encode($ret);
    return;
}
// The passwords are salted and hashed, just for a little security.
$password = hash("sha512", $password);
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, "marvelheroesdb");
// Pull the old password from the DB
$query = "SELECT password FROM saved_config WHERE keyword=?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $keyword);
$stmt->execute();
$stmt->bind_result($saved_password);
$stmt->fetch();

// Check if they match (or if the old one was empty)
$can_save = true;
if (empty($saved_password)) {
    $can_save = true;
} else if ($saved_password != $password) {
    $can_save = false;
}
$stmt->close();
// Write the strings to the database; not a lot of mystery here.
if ($can_save) {
    $query = "DELETE FROM saved_config WHERE keyword=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $keyword);
    $stmt->execute();
    $stmt->close();

    $query = "DELETE FROM saved_links WHERE keyword=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $keyword);
    $stmt->execute();
    $stmt->close();

    $query = "INSERT INTO saved_config(keyword, password, position_grid, costume_grid, level_grid, view_mode, font, include_link, include_tooltips, border_type, flair_grid, border_color, version, half_grids) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sssssiiiiissis", $keyword, $password, $position_grid, $costume_grid, $level_grid, $view_mode, $font, $include_link, $include_tooltips, $border_type, $flair_grid, $border_color, $version, $half_grids);
    $stmt->execute();
    $stmt->close();

    $num_chars = strlen($position_grid) / 6;
    $links = explode("|", $link_grid);
    for ($i = 0; $i < $num_chars; $i++) {
        if (!empty($links[$i]) && strlen($links[$i]) > 0) {
            $query = "INSERT INTO saved_links(keyword, character_index, link) VALUES (?,?,?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("sis", $keyword, $i, $links[$i]);
            $stmt->execute();
            $stmt->close();
        }
    }
    $ret['message'] = "SUCCESS";    
} else {   
    $ret['message'] = "ERROR: Your password is incorrect, or that keyword is already taken.  Please try again.";
}
$mysqli->close();
} catch (Exception $ex) {
    $ret['message'] =  "EXCEPTION: " . $ex->getMessage();
}
echo json_encode($ret);
?>