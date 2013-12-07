<?php

/* 
 *  Interactive Marvel Heroes Forum Signature Generator
    Copyright (C) 2013 Sean McGinnis

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
 
 /*
  * marvelsig.php: This is the module that renders the image.  The output is a PNG file.
  */
  
// Include the convenience class for storing character data
require_once ('marvelheroes_classes.php');
// Include the convenience class for grabbing global values from the php.ini file.
include 'marvelheroes_config.php';

// Connect to SQL
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, "marvelheroesdb");

// Customer error handler to dump out some good bits for debugging.
function customError($errno, $errstr) {
	echo "<b>Error:</b> [$errno] $errstr<br>";
	echo "Ending Script";
	global $characters;
	global $costume_grid;
	echo $costume_grid;
	var_dump(debug_backtrace());
	print_r($characters);
	die();
}
set_error_handler("customError");

// Parse the parameters out of the URL
// For the purposes of this whole file, char_index is a number indicating the character's position in any array.  It is necessarily independent of display order.  So, for exampke, 0 is Black Panther, 1 is Black Widow, and so on. 

// The config_string stored the costume and level information in version 1.  It is supported retroactively, but converted to the newer position/costume/level grids.
$config_string = "";
if (isset($_GET['config'])) {
	$config_string = $_GET['config'];
}

// The position_grid is a list, indexed by char_index, of 6-character codes giving the position of the image in the sig (in grid squares).  The format is XxxYyy, where xx is the column index and yy is the row index.
$position_grid = "";
if (isset($_GET['position_grid'])) {
	$position_grid = $_GET['position_grid'];
}

// The level_grid is a list of character levels, indexed by char_index.  The codes are 3-characters, and account for prestige level, i.e. level_code=(60 * prestige_level) + level.
$level_grid = "";
if (isset($_GET['level_grid'])) {
	$level_grid = $_GET['level_grid'];
}

// The level_grid is a list of character levels, indexed by char_index.  The codes are 3-characters, and account for prestige level, i.e. level_code=(60 * prestige_level) + level.
$flair_grid = "";
if (isset($_GET['flair_grid'])) {
	$flair_grid = $_GET['flair_grid'];
}

// The costume_grid is a list of what costume each character is wearing, indexed by char_index.  These are 2-character codes, and are an integer that maps to a costume entry in the costume DB.
$costume_grid = "";
if (isset($_GET['costume_grid'])) {
	$costume_grid = $_GET['costume_grid'];
}

// The keyword is the "username" that links to a server-side stored configuration
$keyword = "";
if (isset($_GET['keyword'])) {
	$keyword = $_GET['keyword'];
}

// view_mode determines if the sig is always square or can be odd-shaped.  0 is always square, 1 is odd-shaped.
$view_mode = 0;
if (isset($_GET['view_mode'])) {
	$view_mode = intval($_GET['view_mode']);
}
if ($view_mode > 1) {
	$view_mode = 0;
}

// border_type determines what borders are drawn around the character portraits.  0 is black with blue edges, 1 is just black, 2 is no borders.
$border_type = 0;
if (isset($_GET['border_type'])) {
	$border_type = intval($_GET['border_type']);
}
if ($border_type > 2) {
	$border_type = 0;
}

// The numeric index of the font used to draw the level numbers.  This is a table in the database that stores the index and the path to the ttf file.
$font_index = 0;
if (isset($_GET['font'])) {
	$font_index = intval($_GET['font']);
}

// Okay, first thing: load all characters into an array.
$characters = array();
// Order the list by display position, and then numerically by costume.
$query = "SELECT * FROM character_images ORDER BY display_position, costume_index";
$result = $mysqli -> query($query);
while ($myrow = $result -> fetch_assoc()) {
	$char = NULL;
	$char_index = strval($myrow['character_index']);
	$found = false;
	// There are multiple rows per character (one per costume), so we need to search the list and populate the character's costumes when we find a match.
	for ($i = 0; $i < count($characters); $i++) {
		if (strcmp($characters[$i] -> get_char_index(), $char_index) == 0) {
			$found = true;
			$characters[$i] -> set_char_index(strval($myrow['character_index']));
			$characters[$i] -> set_char_name($myrow['character_name']);
			$characters[$i] -> push_cos_indices(strval($myrow['costume_index']));
			$characters[$i] -> push_cos_images("images_new/" . $myrow['image_file']);
			$characters[$i] -> push_cos_names($myrow['costume_name']);
			break;
		}
	}
	// If not found, create a new one.
	if (!$found) {
		$char = new MarvelHero();
		$char -> set_char_index(strval($myrow['character_index']));
		$char -> set_char_name($myrow['character_name']);
		$char -> push_cos_indices(strval($myrow['costume_index']));
		$char -> push_cos_images("images_new/" . $myrow['image_file']);
		$char -> push_cos_names($myrow['costume_name']);
		$char -> set_display_order(intval($myrow['display_position']));
		array_push($characters, $char);
	}
}
$result -> close();

// Okay, first thing: load all characters into an array.
$flair = array();
// Order the list by display position, and then numerically by costume.
$query = "SELECT * FROM flair ORDER BY flair_index";
$result = $mysqli -> query($query);
while ($myrow = $result -> fetch_assoc()) {
	$newflair = new MarvelFlair();
	$newflair -> set_flair_index(intval($myrow['flair_index']));
	$newflair -> set_flair_name($myrow['flair_name']);
	$newflair -> set_flair_file($myrow['flair_file']);
	array_push($flair, $newflair);
}
$result -> close();
$offset = 0;
$character = 0;
$costume = "";
$level = 0;

// If there is a keyword, load the config from the database.  This is pretty straightforward.

if ($keyword != NULL && strlen($keyword) > 0) {
	$query = "SELECT config, chars_per_row, view_mode, font, position_grid, level_grid, costume_grid, border_type, flair_grid FROM saved_config WHERE keyword=?";
	$stmt = $mysqli -> prepare($query);
	$stmt -> bind_param("s", $keyword);
	$stmt -> execute();
	$stmt -> bind_result($db_config, $db_chars_per_row, $db_view_mode, $db_font_index, $db_position_grid, $db_level_grid, $db_costume_grid, $db_border_type, $db_flair_grid);
	$stmt -> fetch();
	$config_string = $db_config;
	if (!empty($db_view_mode)) {
		$view_mode = intval($db_view_mode);
	}
	if (!empty($db_font_index)) {
		$font_index = intval($db_font_index);
	}
	if (!empty($db_position_grid)) {
		$position_grid = $db_position_grid;
	}
	if (!empty($db_costume_grid)) {
		$costume_grid = $db_costume_grid;
	}
	if (!empty($db_level_grid)) {
		$level_grid = $db_level_grid;
	}
	if (!empty($db_border_type)) {
		$border_type = $db_border_type;
	}
	if (!empty($db_flair_grid)) {
		$flair_grid = $db_flair_grid;
	}
	$stmt -> close();
}

// If this is a v1 sig, we need to convert the sig to the new format.
if (strlen($config_string) > 0 && strlen($position_grid) <= 0) {
	convertLegacyConfig($characters, $config_string);
}

$num_characters = count($characters);
$grid_width = 0;
$grid_height = 0;
// Determine the grid dimensions by iterating through the position grid and finding the max X and Y coordinates.
for ($i = 0; $i < count($characters); $i++) {
	$grid_tag = getGridValue($position_grid, $i, 6);
	$x = intval(substr($grid_tag, 1, 2));
	$y = intval(substr($grid_tag, 4, 2));
	if ($x > $grid_width) {
		$grid_width = $x;
	}
	if ($y > $grid_height) {
		$grid_height = $y;
	}
}
// Bump it up by one (since the grid is 0-based)
$grid_width = $grid_width + 1;
$grid_height = $grid_height + 1;

// Calculate the image with.  Thanks to @zztodd, all images are 45x45, so the math is easy.
$image_width = $grid_width * 45;
$image_height = $grid_height * 45;
$x_offset = 0;
$y_offset = 0;

// Allocate a canvas to draw on
$canvas = imagecreatetruecolor($image_width, $image_height);
imagesavealpha($canvas, true);

// Make the canvas transparent
$trans_colour = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
imagefill($canvas, 0, 0, $trans_colour);

// Allocate the colors we will need for the borders and the level numbers
$white = imagecolorallocate($canvas, 255, 255, 255);
$black = imagecolorallocate($canvas, 0, 0, 0);
$green = imagecolorallocate($canvas, 0, 242, 1);
$blue = imagecolorallocate($canvas, 30, 144, 255);
$purple = imagecolorallocate($canvas, 203, 0, 254);
$orange = imagecolorallocate($canvas, 250, 95, 3);
$red = imagecolorallocate($canvas, 254, 0, 0);
$frame = imagecolorallocate($canvas, 0, 170, 255);
$font = 'fonts/arialbd.ttf';
// Load the font that was passed in (if it exists)
if ($font_index > 0) {
	$query = "SELECT font_file FROM fonts WHERE font_index=?";
	$stmt = $mysqli -> prepare($query);
	$stmt -> bind_param("i", $font_index);
	$stmt -> execute();
	$stmt -> bind_result($db_fontfile);
	$stmt -> fetch();
	if (!empty($db_fontfile)) {
		$font = strval($db_fontfile);
	}
	$stmt -> close();
}

$i = 0;
$frame_type = 0;
$x_grid = 0;
$y_grid = 0;
$flair_image = "";
// Iterate over the grid squares
for ($y_grid = 0; $y_grid < $grid_height; $y_grid++) {
	for ($x_grid = 0; $x_grid < $grid_width; $x_grid++) {
		$imagefile = "";		
		// Create a grid_tag, which is the 6-character code for this square.
		$grid_tag = "X" . str_pad(strval($x_grid), 2, "0", STR_PAD_LEFT) . "Y" . str_pad(strval($y_grid), 2, "0", STR_PAD_LEFT);
		$char_index = -1;
		// String magic: the char index is the position of the grid tag in the position grid, divided by 6.  If it's false, then the grid square is empty.
		if(strpos($position_grid, $grid_tag) === false)
		{
			$char_index = -1;
		}
		else
		{
			$char_index = strpos($position_grid, $grid_tag)/6;		
		}
		// Find the character with this char index; ideally, this would just be the index into the character list, but there's an issue I haven't quite fixed that requires me to load the characters in display order.
		$char_i = 0;
		for ($i = 0; $i < $num_characters; $i++) {
			if (intval($characters[$i] -> get_char_index()) == $char_index) {
				$char_i = $i;
			}
		}
		$level = -1;
		$flair_index = 0;
		// If there is no character, load a blank image.
		if ($char_index < 0) {
			if ($view_mode == 0) {
				$imagefile = "images_new/blank.jpg";
			}
		} else {
			// Otherwise, get the character's costume index and level from those grids.
			$cos_index = getGridValue($costume_grid, $char_index, 2);
			for ($k = 0; $k < $characters[$char_i] -> get_cos_images_count(); $k++) {
				if ($characters[$char_i] -> pop_cos_indices($k) == $cos_index) {
					$imagefile = $characters[$char_i] -> pop_cos_images($k);
				}
			}
			$level = intval(getGridValue($level_grid, $char_index, 3));
			$flair_index = intval(getGridValue($flair_grid, $char_index, 2));
		}

		// Calculate drawing coordinates
		$x_offset = $x_grid * 45;
		$y_offset = $y_grid * 45;
		// Draw the character portrait.
		if (strlen($imagefile) > 0) {
			$im = imagecreatefromjpeg($imagefile);
			imagecopy($canvas, $im, $x_offset, $y_offset, 0, 0, 45, 45);
			imagedestroy($im);
		} else {
			continue;
		}
		// Render the level text, adjusted for prestige level.
		if ($level > 0) {
			$color = $white;
			if ($level > 60 && $level <= 120) {
				$color = $green;
				$level = $level - 60;
			} else if ($level > 120 && $level <= 180) {
				$color = $blue;
				$level = $level - 120;
			} else if ($level > 180 && $level <= 240) {
				$color = $purple;
				$level = $level - 180;
			} else if ($level > 240 && $level <= 300) {
				$color = $orange;
				$level = $level - 240;
			} else if ($level > 300) {
				$color = $red;
				$level = $level - 300;
				if ($level > 60) {
					$level = 60;
				}
			}
			$text = strval($level);
			imagettftext($canvas, 12, 0, $x_offset + 24, $y_offset + 40, $black, $font, $text);
			imagettftext($canvas, 12, 0, $x_offset + 25, $y_offset + 41, $color, $font, $text);
		}
		// Draw the rectangle.  I was doing this with thickness, but I couldn't quite get a handle, so it's two rectangles.
		if ($border_type == 0 || $border_type == 1) {
			imagerectangle($canvas, $x_offset, $y_offset, $x_offset + 44, $y_offset + 44, $black);
			imagerectangle($canvas, $x_offset + 1, $y_offset + 1, $x_offset + 43, $y_offset + 43, $black);
		}
		// If the border is 0, there's a blue highlight -- do that.
		if ($border_type == 0) {
			// If the image is square, just draw the edges.
			if ($view_mode == 0) {
				imagesetthickness($canvas, 1);
				if ($x_grid == 0) {
					imageline($canvas, $x_offset, $y_offset, $x_offset, $y_offset + 44, $frame);
				}
				if ($y_grid == 0) {
					imageline($canvas, $x_offset, $y_offset, $x_offset + 44, $y_offset, $frame);
				}
				if ($x_grid == $grid_width - 1) {
					imageline($canvas, $x_offset + 44, $y_offset, $x_offset + 44, $y_offset + 44, $frame);
				}
				if ($y_grid == $grid_height - 1) {
					imageline($canvas, $x_offset, $y_offset + 44, $x_offset + 44, $y_offset + 44, $frame);
				}
			} else {
				// If the image is not square, then check all of the square's neighbors.  If there is no neighbor, then draw the border.
				imagesetthickness($canvas, 1);
				if ($x_grid == 0) {
					imageline($canvas, $x_offset, $y_offset, $x_offset, $y_offset + 44, $frame);
				}
				if ($y_grid == 0) {
					imageline($canvas, $x_offset, $y_offset, $x_offset + 44, $y_offset, $frame);
				}
				if ($x_grid > 0) {
					$grid_cand = "X" . str_pad(strval($x_grid - 1), 2, "0", STR_PAD_LEFT) . "Y" . str_pad(strval($y_grid), 2, "0", STR_PAD_LEFT);
					if (strpos($position_grid, $grid_cand) === false) {
						imageline($canvas, $x_offset, $y_offset, $x_offset, $y_offset + 44, $frame);
					}
				}
				$grid_cand = "X" . str_pad(strval($x_grid + 1), 2, "0", STR_PAD_LEFT) . "Y" . str_pad(strval($y_grid), 2, "0", STR_PAD_LEFT);
				if (strpos($position_grid, $grid_cand) === false) {
					imageline($canvas, $x_offset + 44, $y_offset, $x_offset + 44, $y_offset + 44, $frame);
				}
				if ($y_grid > 0) {
					$grid_cand = "X" . str_pad(strval($x_grid), 2, "0", STR_PAD_LEFT) . "Y" . str_pad(strval($y_grid - 1), 2, "0", STR_PAD_LEFT);
					if (strpos($position_grid, $grid_cand) === false) {
						imageline($canvas, $x_offset, $y_offset, $x_offset + 44, $y_offset, $frame);
					}
				}
				$grid_cand = "X" . str_pad(strval($x_grid), 2, "0", STR_PAD_LEFT) . "Y" . str_pad(strval($y_grid + 1), 2, "0", STR_PAD_LEFT);
				if (strpos($position_grid, $grid_cand) === false) {
					imageline($canvas, $x_offset, $y_offset + 44, $x_offset + 44, $y_offset + 44, $frame);
				}
			}
		}
		if($flair_index > 0)
		{
			$flair_image = "glyphicons_free/glyphicons/png/" . $flair[$flair_index]->get_flair_file();
			list($fwidth, $fheight) = getimagesize("glyphicons_free/glyphicons/png/" . $flair[$flair_index]->get_flair_file());
			$xoff = 12 - $fwidth;
			$yoff = 12 - $fheight;
			$im = imagecreatefrompng($flair_image);
			imagecopy($canvas, $im, $x_offset + 31 + $xoff, $y_offset + 3 + $yoff, 0, 0, $fwidth, $fheight);
			imagedestroy($im);
		}
	}
}
$mysqli -> close();
//Header e output
header('Content-type: image/png');
// Create the PNG
imagepng($canvas);
imagedestroy($canvas);

// This is a convenience function to get a value from a "grid" (really just fixed-length token strings)
function getGridValue($inGrid, $inIndex, $inGridCellSize) {
	if (strlen($inGrid) <= ($inIndex * $inGridCellSize)) {
		return NULL;
	}
	$offset = $inIndex * $inGridCellSize;
	return substr($inGrid, $offset, $inGridCellSize);
}

// This is a convenience function to set a value in a "grid" (really just fixed-length token strings)
function setGridValue($inGrid, $inValue, $inIndex, $inGridCellSize) {
	if (strlen($inGrid) <= ($inIndex * $inGridCellSize)) {
		return $inGrid;
	}
	$offset = $inIndex * $inGridCellSize;
	$new_val = str_pad($inValue, $inGridCellSize, "0", STR_PAD_LEFT);
	return substr($inGrid, 0, $offset) . $new_val . substr($inGrid, $offset + $inGridCellSize);
}

// This function converts a V1 config to a V2 config.
function convertLegacyConfig($characters, $config_string) {
	$num_characters = count($characters);
	$new_level_grid = "";
	// Create default (all 0) grids for position, costumes, and level
	for ($i = 0; $i < $num_characters; $i++) {
		$new_level_grid = $new_level_grid . "000";
	}
	$new_position_grid = "";
	for ($i = 0; $i < $num_characters; $i++) {
		$new_position_grid = $new_position_grid . "X-1Y-1";
	}
	$new_costume_grid = "";
	for ($i = 0; $i < $num_characters; $i++) {
		$new_costume_grid = $new_costume_grid . "00";
	}

	$x = 0;
	$y = 0;

	// Iterate through the characters by character index.
	for ($i = 0; $i < $num_characters; $i++) {
		for ($j = 0; $j < $num_characters; $j++) {
			if ($characters[$j] -> get_display_order() == $i) {
				$char_index = $characters[$i] -> get_char_index();
				break;
			}
		}

		// Parse out the level from the old string, and put it in the new level grid.
		if ($config_string != NULL && strlen($config_string) > (5 * intval($char_index))) {
			$level = substr($config_string, (5 * intval($char_index)) + 2, 3);
			$new_level_grid = setGridValue($new_level_grid, str_pad($level, 3, "0", STR_PAD_LEFT), intval($char_index), 3);
		} else {
			$new_level_grid = $new_level_grid . "000";
			$new_level_grid = setGridValue($new_level_grid, "000", intval($char_index), 3);
		}

		// Put the character in position, if the costume is not set to 99 (do not render)
		$grid_tag = "X-1Y-1";
		if ($config_string != NULL && strlen($config_string) > (5 * intval($char_index))) {
			$cos = substr($config_string, 5 * intval($char_index), 2);
			if (intval($cos) != 99) {
				// Set the costume into the costume grid
				$new_costume_grid = setGridValue($new_costume_grid, str_pad($cos, 2, "0", STR_PAD_LEFT), intval($char_index), 2);
				$grid_tag = "X" . str_pad($x, 2, "0", STR_PAD_LEFT) . "Y" . str_pad($y, 2, "0", STR_PAD_LEFT);
				$x += 1;
				// Default to a 14-column layout, like V1.
				if ($x > 13) {
					$y += 1;
					$x = 0;
				}
			} else {
				$new_costume_grid = setGridValue($new_costume_grid, "00", intval($char_index), 2);
			}
		} else {
			$new_costume_grid = setGridValue($new_costume_grid, "00", intval($char_index), 2);
		}
		$new_position_grid = setGridValue($new_position_grid, $grid_tag, intval($char_index), 6);
	}
	if ($x > 0) {
		$y += 1;
	}
	global $position_grid;
	global $costume_grid;
	global $level_grid;
	// Set the global variables to the new values.
	$position_grid = $new_position_grid;
	$costume_grid = $new_costume_grid;
	$level_grid = $new_level_grid;
}
?>