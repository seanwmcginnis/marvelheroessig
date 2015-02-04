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
    var_dump(debug_backtrace());
    die();
}

function process_error_backtrace($errno, $errstr, $errfile, $errline, $errcontext) {
    if (!(error_reporting() & $errno))
        return;
    switch ($errno) {
        case E_WARNING :
        case E_USER_WARNING :
        case E_STRICT :
        case E_NOTICE :
        case E_USER_NOTICE :
            $type = 'warning';
            $fatal = false;
            break;
        default :
            $type = 'fatal error';
            $fatal = true;
            break;
    }
    $trace = array_reverse(debug_backtrace());
    array_pop($trace);
    if (php_sapi_name() == 'cli') {
        echo 'Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n";
        foreach ($trace as $item)
            echo '  ' . (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()' . "\n";
    } else {
        echo '<p class="error_backtrace">' . "\n";
        echo '  Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n";
        echo '  <ol>' . "\n";
        foreach ($trace as $item)
            echo '    <li>' . (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()</li>' . "\n";
        echo '  </ol>' . "\n";
        echo '</p>' . "\n";
    }
    if (ini_get('log_errors')) {
        $items = array();
        foreach ($trace as $item)
            $items[] = (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()';
        $message = 'Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ': ' . join(' | ', $items);
        error_log($message);
    }
    if ($fatal)
        exit(1);
}

if (!function_exists('imagettftextblur')) {

    function imagettftextblur(&$image, $size, $angle, $x, $y, $color, $fontfile, $text, $blur_intensity = null) {
        $blur_intensity = !is_null($blur_intensity) && is_numeric($blur_intensity) ? (int) $blur_intensity : 0;
        if ($blur_intensity > 0) {
            $text_shadow_image = imagecreatetruecolor(imagesx($image), imagesy($image));
            imagefill($text_shadow_image, 0, 0, imagecolorallocate($text_shadow_image, 0x00, 0x00, 0x00));
            imagettftext($text_shadow_image, $size, $angle, $x, $y, imagecolorallocate($text_shadow_image, 0xFF, 0xFF, 0xFF), $fontfile, $text);
            for ($blur = 1; $blur <= $blur_intensity; $blur++)
                imagefilter($text_shadow_image, IMG_FILTER_GAUSSIAN_BLUR);
            for ($x_offset = 0; $x_offset < imagesx($text_shadow_image); $x_offset++) {
                for ($y_offset = 0; $y_offset < imagesy($text_shadow_image); $y_offset++) {
                    $visibility = (imagecolorat($text_shadow_image, $x_offset, $y_offset) & 0xFF) / 255;
                    if ($visibility > 0)
                        imagesetpixel($image, $x_offset, $y_offset, imagecolorallocatealpha($image, ($color >> 16) & 0xFF, ($color >> 8) & 0xFF, $color & 0xFF, (1 - $visibility) * 127));
                }
            }
            imagedestroy($text_shadow_image);
        } else
            return imagettftext($image, $size, $angle, $x, $y, $color, $fontfile, $text);
    }

}

set_error_handler('process_error_backtrace');

function HEX2RGB($color) {
    $color = str_replace("#", "", $color);
    $color_array = array();
    $hex_color = strtoupper($color);
    for ($i = 0; $i < 6; $i++) {
        $hex = substr($hex_color, $i, 1);
        switch ($hex) {
            case "A" :
                $num = 10;
                break;
            case "B" :
                $num = 11;
                break;
            case "C" :
                $num = 12;
                break;
            case "D" :
                $num = 13;
                break;
            case "E" :
                $num = 14;
                break;
            case "F" :
                $num = 15;
                break;
            default :
                $num = $hex;
                break;
        }
        array_push($color_array, $num);
    }
    $R = (($color_array[0] * 16) + $color_array[1]);
    $G = (($color_array[2] * 16) + $color_array[3]);
    $B = (($color_array[4] * 16) + $color_array[5]);
    return array($R, $G, $B);
    unset($color_array, $hex, $R, $G, $B);
}

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
if ($border_type > 5) {
    $border_type = 0;
}

// $border_color determines what color borders are drawn around the character portraits.
$border_color = "";
if (isset($_GET['border_color'])) {
    $border_color = $_GET['border_color'];
}

// The numeric index of the font used to draw the level numbers.  This is a table in the database that stores the index and the path to the ttf file.
$font_index = 0;
if (isset($_GET['font'])) {
    $font_index = intval($_GET['font']);
}

$version = 0;
if (isset($_GET['version'])) {
    $version = intval($_GET['version']);
}

$half_grids = "";
if (isset($_GET['half_grids'])) {
    $half_grids = $_GET['half_grids'];
}


// Okay, first thing: load all characters into an array.
$characters = array();
// Order the list by display position, and then numerically by costume.
$query = "SELECT * FROM character_images ORDER BY display_position, costume_index";
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

// Okay, first thing: load all characters into an array.
$flair = array();
// Order the list by display position, and then numerically by costume.
$query = "SELECT * FROM flair ORDER BY flair_index";
$result = $mysqli->query($query);
while ($myrow = $result->fetch_assoc()) {
    $newflair = new MarvelFlair();
    $flair_index = intval($myrow['flair_index']);
    $newflair->set_flair_index($flair_index);
    $newflair->set_flair_name($myrow['flair_name']);
    $newflair->set_flair_file($myrow['flair_file']);
    $newflair->set_flair_x_offset(intval($myrow['x_offset']));
    $newflair->set_flair_y_offset(intval($myrow['y_offset']));
    $newflair->set_flair_position(intval($myrow['flair_position']));
    $flair[$flair_index] = $newflair;
}
$result->close();
$offset = 0;
$character = 0;
$costume = "";
$level = 0;

// If there is a keyword, load the config from the database.  This is pretty straightforward.

if ($keyword != NULL && strlen($keyword) > 0) {
    $query = "SELECT config, chars_per_row, view_mode, font, position_grid, level_grid, costume_grid, border_type, flair_grid, border_color, version, half_grids FROM saved_config WHERE keyword=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $keyword);
    $stmt->execute();
    $stmt->bind_result($db_config, $db_chars_per_row, $db_view_mode, $db_font_index, $db_position_grid, $db_level_grid, $db_costume_grid, $db_border_type, $db_flair_grid, $db_border_color, $db_version, $db_half_grids);
    $stmt->fetch();
    $config_string = $db_config;
    if (isset($db_view_mode)) {
        $view_mode = intval($db_view_mode);
    }
    if (isset($db_font_index)) {
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
    if (isset($db_border_type)) {
        $border_type = $db_border_type;
    }
    if (!empty($db_flair_grid)) {
        $flair_grid = $db_flair_grid;
    }
    if (!empty($db_border_color)) {
        $border_color = $db_border_color;
    }
    if (isset($db_version)) {
        $version = intval($db_version);
    }
    if (isset($db_half_grids)) {
        $half_grids = $db_half_grids;
    }
    $stmt->close();
}

// If this is a v1 sig, we need to convert the sig to the new format.
if (strlen($config_string) > 0 && strlen($position_grid) <= 0) {
    convertLegacyConfig($characters, $config_string);
}

$num_characters = count($characters);
$flair_grid = convertLegacyFlairString($flair_grid, $version, $num_characters);

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

$warning_font = 'fonts/FF.ttf';

$font = 'fonts/arialbd.ttf';
// Load the font that was passed in (if it exists)
if ($font_index > 0) {
    $query = "SELECT font_file FROM fonts WHERE font_index=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $font_index);
    $stmt->execute();
    $stmt->bind_result($db_fontfile);
    $stmt->fetch();
    if (!empty($db_fontfile)) {
        $font = strval($db_fontfile);
    }
    $stmt->close();
}
$warning = "THIS SIG IS GOING AWAY.  PLEASE UPDATE!";

$box = imagettfbbox(16, 0, $warning_font, $warning);
$text_width = abs($box[4] - $box[0]);
$text_height = abs($box[5] - $box[1]);

if($image_width < $text_width + 20)
{
    $image_width = $text_width + 20;
}
if($image_height < $text_height + 20)
{
    $image_height = $text_height + 20;
}
// Allocate a canvas to draw on
$canvas = imagecreatetruecolor($image_width, $image_height);
// imagesavealpha($canvas, true);
// Make the canvas transparent
$trans_colour = imagecolorallocate($canvas, 231, 246, 255);
imagecolortransparent($canvas, $trans_colour);
imagefill($canvas, 0, 0, $trans_colour);


// Allocate the colors we will need for the borders and the level numbers
$white = imagecolorallocate($canvas, 255, 255, 255);
$black = imagecolorallocate($canvas, 0, 0, 0);
$green = imagecolorallocate($canvas, 0, 242, 1);
$blue = imagecolorallocate($canvas, 30, 144, 255);
$purple = imagecolorallocate($canvas, 203, 0, 254);
$orange = imagecolorallocate($canvas, 250, 95, 3);
$red = imagecolorallocate($canvas, 254, 0, 0);
$yellow = imagecolorallocate($canvas, 222, 202, 11);
$frame = imagecolorallocate($canvas, 0, 170, 255);
if (strlen($border_color) > 0) {
    $colors = HEX2RGB($border_color);
    $frame = imagecolorallocate($canvas, $colors[0], $colors[1], $colors[2]);
}

$i = 0;
$frame_type = 0;
$x_grid = 0;
$y_grid = 0;
$flair_image = "";
// Iterate over the grid squares
for ($y_grid = 0; $y_grid < $grid_height; $y_grid++) {
    $xoff_image = 0;
    for ($x_grid = 0; $x_grid < $grid_width; $x_grid++) {
        $imagefile = "";
        // Create a grid_tag, which is the 6-character code for this square.
        $grid_tag = "X" . str_pad(strval($x_grid), 2, "0", STR_PAD_LEFT) . "Y" . str_pad(strval($y_grid), 2, "0", STR_PAD_LEFT);
        $char_index = -1;
        // String magic: the char index is the position of the grid tag in the position grid, divided by 6.  If it's false, then the grid square is empty.
        if (strpos($position_grid, $grid_tag) === false) {
            $char_index = -1;
        } else {
            $char_index = strpos($position_grid, $grid_tag) / 6;
        }
        // Find the character with this char index; ideally, this would just be the index into the character list, but there's an issue I haven't quite fixed that requires me to load the characters in display order.
        $char_i = 0;
        for ($i = 0; $i < $num_characters; $i++) {
            if (intval($characters[$i]->get_char_index()) == $char_index) {
                $char_i = $i;
            }
        }
        $level = -1;
        $flair_index = 0;
        $lowerleft_flair_index = 0;
        $upperleft_flair_index = 0;
        // If there is no character, load a blank image.
        if ($char_index < 0) {
            if ($view_mode == 0) {
                $imagefile = "images_new/blank.jpg";
            }
        } else {
            // Otherwise, get the character's costume index and level from those grids.
            $cos_index = getGridValue($costume_grid, $char_index, 2);
            for ($k = 0; $k < $characters[$char_i]->get_cos_images_count(); $k++) {
                if ($characters[$char_i]->pop_cos_indices($k) == $cos_index) {
                    $imagefile = $characters[$char_i]->pop_cos_images($k);
                }
            }
            $level = intval(getGridValue($level_grid, $char_index, 3));

            $flair_index = intval(getGridValue($flair_grid, 4 * $char_index, 3));
            $lowerleft_flair_index = intval(getGridValue($flair_grid, (4 * $char_index) + 1, 3));
            $upperleft_flair_index = intval(getGridValue($flair_grid, (4 * $char_index) + 2, 3));
        }

        // Calculate drawing coordinates
        $x_offset = $xoff_image;
        $y_offset = $y_grid * 45;
        // Draw the character portrait.
        if (strlen($imagefile) > 0) {
            $im = imagecreatefromjpeg($imagefile);
            if ($border_type == 4 || $border_type == 5) {
                imagecopy($canvas, $im, $x_offset + 1, $y_offset + 1, 1, 1, 43, 43);
            } else {
                imagecopy($canvas, $im, $x_offset, $y_offset, 0, 0, 45, 45);
            }
            imagedestroy($im);
            $xoff_image += 45;
        } else {
            if (strpos($half_grids, $grid_tag) === false) {
                $xoff_image += 45;
            } else {
                $xoff_image += 22.5;
            }
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
            } else if ($level > 300 && $level <= 360) {
                $color = $red;
                $level = $level - 300;
            } else if ($level > 360) {
                $color = $yellow;
                $level = $level - 360;
                if ($level > 60) {
                    $level = 60;
                }
            }
            $text = strval($level);
            $box = imagettfbbox(12, 0, $font, $text);
            $text_width = abs($box[4] - $box[0]);
            $text_height = abs($box[5] - $box[1]);
            if ($color == $yellow) {
                imagettftextblur($canvas, 12, 0, $x_offset + 45 - $text_width - 4, $y_offset + 42, $color, $font, $text, 5); // 1 can be higher to increase blurriness of the glow
                imagettftextblur($canvas, 12, 0, $x_offset + 45 - $text_width - 4, $y_offset + 42, $color, $font, $text);
            }
            imagettftext($canvas, 12, 0, $x_offset + 45 - $text_width - 4, $y_offset + 42, $black, $font, $text);
            imagettftext($canvas, 12, 0, $x_offset + 45 - $text_width - 4, $y_offset + 41, $color, $font, $text);
            // imagettftext($canvas, 12, 0, $x_offset + 25, $y_offset + 41, $color, $font, $text);
        }
        if ($flair_index > 0 && !empty($flair[$flair_index])) {
            $flair_image = "glyphicons_free/glyphicons/png/" . $flair[$flair_index]->get_flair_file();
            list($fwidth, $fheight) = getimagesize("glyphicons_free/glyphicons/png/" . $flair[$flair_index]->get_flair_file());
            $xoff = 12 - $fwidth;
            $yoff = 12 - $fheight;
            $im = imagecreatefrompng($flair_image);
            imagecopy($canvas, $im, $x_offset + 31 + $xoff, $y_offset + 3 + $yoff, 0, 0, $fwidth, $fheight);
            imagedestroy($im);
        }
        $line_drawn = 0;
        if ($lowerleft_flair_index > 0 && !empty($flair[$lowerleft_flair_index])) {
            $flair_image = "glyphicons_free/glyphicons/png/" . $flair[$lowerleft_flair_index]->get_flair_file();
            list($fwidth, $fheight) = getimagesize("glyphicons_free/glyphicons/png/" . $flair[$lowerleft_flair_index]->get_flair_file());
            $xoff = 0 + $flair[$lowerleft_flair_index]->get_flair_x_offset();
            $yoff = 45 - $fheight - $flair[$lowerleft_flair_index]->get_flair_y_offset();
            $im = imagecreatefrompng($flair_image);
            if ($border_type == 4 || $border_type == 5) {
                if ($xoff == 0) {
                    imagecopy($canvas, $im, $x_offset + $xoff + 1, $y_offset + $yoff - 1, 1, 1, $fwidth - 1, $fheight - 1);
                } else {
                    imagecopy($canvas, $im, $x_offset + $xoff, $y_offset + $yoff, 0, 0, $fwidth, $fheight);
                }
            } else {
                imagecopy($canvas, $im, $x_offset + $xoff, $y_offset + $yoff, 0, 0, $fwidth, $fheight);
            }
            imagedestroy($im);
        }
        if ($upperleft_flair_index > 0 && !empty($flair[$upperleft_flair_index])) {
            $flair_image = "glyphicons_free/glyphicons/png/" . $flair[$upperleft_flair_index]->get_flair_file();
            if ($flair[$upperleft_flair_index]->get_flair_position() == 0) {
                list($fwidth, $fheight) = getimagesize("glyphicons_free/glyphicons/png/" . $flair[$upperleft_flair_index]->get_flair_file());
                $xoff = 12 - $fwidth;
                $yoff = 12 - $fheight;
                $im = imagecreatefrompng($flair_image);
                imagecopy($canvas, $im, $x_offset + 2 + $xoff, $y_offset + 3 + $yoff, 0, 0, $fwidth, $fheight);
                imagedestroy($im);
            } else {
                list($fwidth, $fheight) = getimagesize("glyphicons_free/glyphicons/png/" . $flair[$upperleft_flair_index]->get_flair_file());
                $xoff = 24 - $fwidth;
                $yoff = 24 - $fheight;
                $im = imagecreatefrompng($flair_image);
                imagecopy($canvas, $im, $x_offset + $xoff, $y_offset + $yoff, 0, 0, $fwidth, $fheight);
                imagedestroy($im);
            }
        }
        // Draw the rectangle.  I was doing this with thickness, but I couldn't quite get a handle, so it's two rectangles.
        if ($border_type == 0 || $border_type == 1) {
            imagerectangle($canvas, $x_offset, $y_offset, $x_offset + 44, $y_offset + 44, $black);
            imagerectangle($canvas, $x_offset + 1, $y_offset + 1, $x_offset + 43, $y_offset + 43, $black);
        }
        /* if ($border_type == 4 || $border_type == 5) {
          imagerectangle($canvas, $x_offset, $y_offset, $x_offset + 44, $y_offset + 44, $trans_colour);
          imagerectangle($canvas, $x_offset + 1, $y_offset + 1, $x_offset + 43, $y_offset + 43, $trans_colour);
          } */
        if ($border_type == 3) {
            imagerectangle($canvas, $x_offset, $y_offset, $x_offset + 44, $y_offset + 44, $black);
        }
        $lowerleft_clipped = FALSE;
        $upperleft_clipped = FALSE;

        if ($lowerleft_flair_index > 0 && $flair[$lowerleft_flair_index]->get_flair_y_offset() == 0 && $flair[$lowerleft_flair_index]->get_flair_x_offset() == 0) {
            $lowerleft_clipped = TRUE;
        }

        if ($upperleft_flair_index > 0 && $flair[$upperleft_flair_index]->get_flair_position() == 2) {
            $upperleft_clipped = TRUE;
        }

        if ($border_type == 3) {
            if ($upperleft_clipped > 0) {
                imageline($canvas, $x_offset + 22, $y_offset + 1, $x_offset + 43, $y_offset + 1, $frame);
            } else {
                imageline($canvas, $x_offset + 1, $y_offset + 1, $x_offset + 43, $y_offset + 1, $frame);
            }

            imageline($canvas, $x_offset + 43, $y_offset + 1, $x_offset + 43, $y_offset + 43, $frame);

            if ($lowerleft_clipped > 0) {
                imageline($canvas, $x_offset + 43, $y_offset + 43, $x_offset + 22, $y_offset + 43, $frame);
                imageline($canvas, $x_offset + 22, $y_offset + 43, $x_offset + 1, $y_offset + 22, $frame);
                if ($upperleft_clipped > 0) {
                    imageline($canvas, $x_offset + 1, $y_offset + 22, $x_offset + 22, $y_offset + 1, $frame);
                } else {
                    imageline($canvas, $x_offset + 1, $y_offset + 22, $x_offset + 1, $y_offset + 1, $frame);
                }
            } else {
                imageline($canvas, $x_offset + 43, $y_offset + 43, $x_offset + 1, $y_offset + 43, $frame);
                if ($upperleft_clipped > 0) {
                    imageline($canvas, $x_offset + 1, $y_offset + 43, $x_offset + 1, $y_offset + 22, $frame);
                    imageline($canvas, $x_offset + 1, $y_offset + 22, $x_offset + 22, $y_offset + 1, $frame);
                } else {
                    imageline($canvas, $x_offset + 1, $y_offset + 43, $x_offset + 1, $y_offset + 1, $frame);
                }
            }
        }

        if ($border_type == 5) {
            if ($lowerleft_clipped > 0) {
                imageline($canvas, $x_offset + 22, $y_offset + 43, $x_offset + 1, $y_offset + 22, $frame);
            }
            if ($upperleft_clipped > 0) {
                imageline($canvas, $x_offset + 1, $y_offset + 22, $x_offset + 22, $y_offset + 1, $frame);
            }
            imagerectangle($canvas, $x_offset + 1, $y_offset + 1, $x_offset + 43, $y_offset + 43, $frame);
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
    }
}
$box = imagettfbbox(16, 0, $warning_font, $warning);
$text_width = abs($box[4] - $box[0]);
$text_height = abs($box[5] - $box[1]);
imagettftext($canvas, 12, 0, 11, 11 + $text_height, $black, $warning_font, $warning);
imagettftext($canvas, 12, 0, 10, 10 + $text_height, $red, $warning_font, $warning);

$mysqli->close();
//Header e output
header('Content-type: image/png');
// Create the PNG
imagepng($canvas);
imagedestroy($canvas);

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
            if ($characters[$j]->get_display_order() == $i) {
                $char_index = $characters[$i]->get_char_index();
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