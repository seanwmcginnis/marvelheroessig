<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
// Include the convenience class for storing character data
require_once ('marvelheroes_classes.php');
// Include the convenience class for grabbing global values from the php.ini file.
include 'marvelheroes_config.php';

echo "<html><head></head><body>";
// The keyword is the "username" that links to a server-side stored configuration
$keyword = "";
if (isset($_GET['keyword'])) {
    $keyword = $_GET['keyword'];
}
$tip_type = 0;
if (isset($_GET['tip_type'])) {
    $tip_type = $_GET['tip_type'];
}
if (isset($keyword)) {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, "marvelheroesdb");
// Okay, first thing: load all characters into an array.
    $characters = array();
// Order the list by display position, and then numerically by costume.
    $query = "SELECT * FROM character_images ORDER BY character_index, costume_index";
    $result = $mysqli->query($query);
    while ($myrow = $result->fetch_assoc()) {
        $char = NULL;
        $char_index = strval($myrow['character_index']);
        $found = false;
// There are multiple rows per character (one per costume), so we need to search the list and populate the character's costumes when we find a match.
        for ($i = 0; $i < count($characters); $i++) {
            if (strcmp($characters[$i]->get_char_index(), $char_index) == 0) {
                $found = true;
                $characters[$i]->set_char_index(strval($myrow['character_index']));
                $characters[$i]->set_char_name($myrow['character_name']);
                $characters[$i]->push_cos_indices(strval($myrow['costume_index']));
                $characters[$i]->push_cos_images("images_new/" . $myrow['image_file']);
                $characters[$i]->push_cos_names($myrow['costume_name']);
                break;
            }
        }
// If not found, create a new one.
        if (!$found) {
            $char = new MarvelHero();
            $char->set_char_index(strval($myrow['character_index']));
            $char->set_char_name($myrow['character_name']);
            $char->push_cos_indices(strval($myrow['costume_index']));
            $char->push_cos_images("images_new/" . $myrow['image_file']);
            $char->push_cos_names($myrow['costume_name']);
            $char->set_display_order(intval($myrow['display_position']));
            array_push($characters, $char);
        }
    }
    $result->close();

    $query = "SELECT position_grid, level_grid, costume_grid, half_grids FROM saved_config WHERE keyword=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $keyword);
    $stmt->execute();
    $stmt->bind_result($position_grid, $level_grid, $costume_grid, $half_grids);
    $stmt->fetch();
    $stmt->close();

    $query = "SELECT character_index, link FROM saved_links WHERE keyword=? ORDER BY character_index";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $keyword);
    $stmt->execute();
    $stmt->bind_result($db_character_index, $db_link);
    $char_index = 0;
    $link_grid = array();
    while ($stmt->fetch()) {
        while ($char_index < $db_character_index) {
            array_push($link_grid, "");
            $char_index += 1;
        }
        array_push($link_grid, $db_link);
    }    
    $stmt->close();

    $mysqli->close();
// If we're doing tooltips, generate a unique name for the image map.
    echo "<map name='marvelmap'>";
    $char_index = 0;
    $cos_index = 0;
    $coords = "";
    $grid_tag = "";
    $title = "";
// Iterate through the character indices
    for ($char_index = 0; $char_index < count($characters); $char_index++) {
// Get the location of the character
        $grid_tag = getGridValue($position_grid, $char_index, 6);
        $cos_index = intval(getGridValue($costume_grid, $char_index, 2));
        $level = intval(getGridValue($level_grid, $char_index, 3));
// If the character is not on the grid, then skip
        if (!isset($grid_tag)) {
            $grid_tag = "X-1Y-1";
        }
        if (strcmp($grid_tag, "X-1Y-1") !== 0) {
// Parse the coordinates
            $x = intval(substr($grid_tag, 1, 2));
            $y = intval(substr($grid_tag, 4, 2));
// Put togehter a title, starting with the character's name.
            $title = $characters[$char_index]->get_char_name();
// Find the index of the character's costume in the arrays, and pull it.
            for ($i = 0; $i < $characters[$char_index]->get_cos_names_count(); $i++) {
                if ($characters[$char_index]->pop_cos_indices($i) == $cos_index) {
                    $title = $title . " (" . str_replace("'", "&apos;", $characters[$char_index]->pop_cos_names($i)) . ")";
                    break;
                }
            }
// If we are including the level add that to the title.
            if ($tip_type == 1) {
                $prestige = 0;
                if ($level > 60 && $level <= 120) {
                    $prestige = 1;
                    $level = $level - 60;
                } else if ($level > 120 && $level <= 180) {
                    $prestige = 2;
                    $level = $level - 120;
                } else if ($level > 180 && $level <= 240) {
                    $prestige = 3;
                    $level = $level - 180;
                } else if ($level > 240 && $level <= 300) {
                    $prestige = 4;
                    $level = $level - 240;
                } else if ($level > 300 && $level <= 360) {
                    $prestige = 5;
                    $level = $level - 300;
                } else if ($level > 360) {
                    $prestige = 6;
                    $level = $level - 360;
                    if ($level > 60) {
                        $level = 60;
                    }
                }
                if ($level > 0) {
                    $title = $title . ": Level " . strval($level);
                    if ($prestige == 1) {
                        $title = $title . " Green";
                    } else if ($prestige == 2) {
                        $title = $title . " Blue";
                    } else if ($prestige == 3) {
                        $title = $title . " Purple";
                    } else if ($prestige == 4) {
                        $title = $title . " Orange";
                    } else if ($prestige == 5) {
                        $title = $title . " Red";
                    } else if ($prestige == 6) {
                        $title = $title . " Cosmic";
                    }
                }
            }
// Calculate the coords, which are just 45 * x and 45 * y
            $gridx = 0;
            $gridy = 45 * $y;
            for ($cx = 0; $cx < $x; $cx++) {
                $grid_tag = "X" . str_pad(strval($cx), 2, "0", STR_PAD_LEFT) . "Y" . str_pad(strval($y), 2, "0", STR_PAD_LEFT);
                if (strpos($half_grids, $grid_tag) === false) {
                    $gridx += 45;
                } else {
                    $gridx += 22.5;
                }
            }
            $coords = strval($gridx) . "," . strval($gridy) . "," . strval($gridx + 45) . "," . strval($gridy + 45);

// Add the final map entry to the markdown
            echo "<area shape='rect' coords='" . $coords . "' title='" . $title . "'";
            if (strlen($link_grid[$char_index] > 0)) {
                echo " href='" . $link_grid[$char_index] . "' target='_blank'";
            }
            echo "/>";
        }
    }
// Close the map
    echo "</map>";
}
echo "</body></html>";