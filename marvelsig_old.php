<?php

function customError($errno, $errstr) {
	echo "<b>Error:</b> [$errno] $errstr<br>";
	echo "Ending Script";
	die();
}

set_error_handler("customError");
$num_characters = 28;
$default_num_rows = 2;

$config_string = "";
if (isset($_GET['config'])) {
	$config_string = $_GET['config'];
}
$keyword = "";
if (isset($_GET['keyword'])) {
	$keyword = $_GET['keyword'];
}
$grid_width = 0;

if (isset($_GET['grid_width'])) {
	$grid_width = intval($_GET['grid_width']);	
}

$view_mode = 0;
if (isset($_GET['view_mode'])) {
	$view_mode = intval($_GET['view_mode']);
}
if($view_mode > 1)
{
	$view_mode = 0;
}

$font_index = 0;
if (isset($_GET['font'])) {
	$font_index = intval($_GET['font']);
}

$offset = 0;
$character = 0;
$costume = "";
$level = 0;

include '../marvelheroes_config.php';
$mysqli = new mysqli($db_address, $db_name, $db_user, $db_password);
if ($keyword != NULL && strlen($keyword) > 0) {
	$query = "SELECT config, chars_per_row, view_mode, font FROM saved_config WHERE keyword=?";
	$stmt = $mysqli -> prepare($query);
	$stmt -> bind_param("s", $keyword);
	$stmt -> execute();
	$stmt -> bind_result($db_config, $db_chars_per_row, $db_view_mode, $db_font_index);
	$stmt -> fetch();
	$config_string = $db_config;
	if (!empty($db_chars_per_row)) {
		$grid_width = intval($db_chars_per_row);
	}
	if (!empty($db_view_mode)) {
		$view_mode = intval($db_view_mode);
	}
	if (!empty($db_font_index)) {
		$font_index = intval($db_font_index);
	}
	$stmt -> close();
}

$config_len = strlen($config_string);
$num_displayed_characters = 0;
while ($character < $num_characters) {
	$offset = $character * 5;
	if ($offset >= $config_len) {
		$costume = "99";
		$level = 0;
	} else {
		$costume = substr($config_string, $offset, 2);
		$level = intval(substr($config_string, $offset + 2, 3));
	}
	if ($costume != "99") {
		$num_displayed_characters += 1;
	}
	$character = $character + 1;
}
if($grid_width < 2)
{
	$grid_width = (int)ceil($num_displayed_characters / $default_num_rows);
}
$grid_height = (int)ceil($num_displayed_characters / $grid_width);
$image_width = $grid_width * 45;
$image_height = $grid_height * 45;
$x_offset = 0;
$y_offset = 0;

$canvas = imagecreatetruecolor($image_width, $image_height);
imagesavealpha($canvas, true);

$trans_colour = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
imagefill($canvas, 0, 0, $trans_colour);
$white = imagecolorallocate($canvas, 255, 255, 255);
$black = imagecolorallocate($canvas, 0, 0, 0);
$green = imagecolorallocate($canvas, 0, 255, 0);
$blue = imagecolorallocate($canvas, 100, 100, 255);
$purple = imagecolorallocate($canvas, 255, 100, 255);
$orange = imagecolorallocate($canvas, 255, 157, 30);
$red = imagecolorallocate($canvas, 255, 0, 0);

$font = 'fonts/arialbd.ttf';
if($font_index > 0)
{
	$query = "SELECT font_file FROM fonts WHERE font_index=?";
	$stmt = $mysqli -> prepare($query);
	$stmt -> bind_param("i", $font_index);
	$stmt -> execute();
	$stmt -> bind_result($db_fontfile);
	$stmt -> fetch();
	if(!empty($db_fontfile))
	{
		$font = strval($db_fontfile);
	}
	$stmt->close();
}

$query = "";
$character = 0;
while ($character < $num_characters) {
	$offset = $character * 5;
	if ($offset >= $config_len) {
		$costume = "99";
		$level = 0;
	} else {
		$costume = substr($config_string, $offset, 2);
		$level = intval(substr($config_string, $offset + 2, 3));
	}
	if (intval($costume) == 99) {
		$character += 1;
		continue;
	}
	if (strlen($query) == 0) {
		$query = "SELECT * FROM character_images WHERE (character_index= " . $character . " AND costume_index=" . $costume . ")";
	} else {
		$query = $query . " OR (character_index= " . $character . " AND costume_index=" . $costume . ")";
	}
	$character += 1;
}
$query = $query . " ORDER BY display_position";
$frame_topleft = imagecreatefrompng("images_new/Frame_TopLeft.png");
$frame_top = imagecreatefrompng("images_new/Frame_Top.png");
$frame_topright = imagecreatefrompng("images_new/Frame_TopRight.png");
$frame_left = imagecreatefrompng("images_new/Frame_Left.png");
$frame_center = imagecreatefrompng("images_new/Frame_Center.png");
$frame_right = imagecreatefrompng("images_new/Frame_Right.png");
$frame_bottomleft = imagecreatefrompng("images_new/Frame_BottomLeft.png");
$frame_bottom = imagecreatefrompng("images_new/Frame_Bottom.png");
$frame_bottomright = imagecreatefrompng("images_new/Frame_BottomRight.png");
$frame_leftbottomright = imagecreatefrompng("images_new/Frame_LeftBottomRight.png");

$frame_type = 0;
$result = $mysqli -> query($query);
$x_grid = 0;
$y_grid = 0;

while ($myrow = $result -> fetch_assoc()) {
	$imagefile = "images_new/" . $myrow['image_file'];
	$character = intval($myrow['character_index']);
	$offset = $character * 5;
	if ($offset >= $config_len) {
		$level = 0;
	} else {
		$level = intval(substr($config_string, $offset + 2, 3));
	}
	$x_offset = $x_grid * 45;
	$y_offset = $y_grid * 45;

	$im = imagecreatefromjpeg($imagefile);
	imagecopy($canvas, $im, $x_offset, $y_offset, 0, 0, 45, 45);
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
	$frame_type = 0;
	if ($y_grid == $grid_height - 2 && $view_mode == 1 && $num_displayed_characters % $grid_width > 0) {
		if ($x_grid == 0) {
			imagecopy($canvas, $frame_left, $x_offset, $y_offset, 0, 0, 45, 45);
		} else if ($x_grid == $grid_width - 1) {
			imagecopy($canvas, $frame_bottomright, $x_offset, $y_offset, 0, 0, 45, 45);
		} else if ($x_grid < $num_displayed_characters % $grid_width) {
			imagecopy($canvas, $frame_center, $x_offset, $y_offset, 0, 0, 45, 45);
		} else {
			imagecopy($canvas, $frame_bottom, $x_offset, $y_offset, 0, 0, 45, 45);
		}
	} else if ($x_grid == 0) {
		if ($y_grid == 0) {
			imagecopy($canvas, $frame_topleft, $x_offset, $y_offset, 0, 0, 45, 45);
		} else if ($y_grid == $grid_height - 1) {
			imagecopy($canvas, $frame_bottomleft, $x_offset, $y_offset, 0, 0, 45, 45);
		} else {
			imagecopy($canvas, $frame_left, $x_offset, $y_offset, 0, 0, 45, 45);
		}
	} else if ($x_grid == $grid_width - 1) {
		if ($y_grid == 0) {
			imagecopy($canvas, $frame_topright, $x_offset, $y_offset, 0, 0, 45, 45);
		} else if ($y_grid == $grid_height - 1) {
			imagecopy($canvas, $frame_bottomright, $x_offset, $y_offset, 0, 0, 45, 45);
		} else {
			imagecopy($canvas, $frame_right, $x_offset, $y_offset, 0, 0, 45, 45);
		}
	} else {
		if ($y_grid == 0) {
			imagecopy($canvas, $frame_top, $x_offset, $y_offset, 0, 0, 45, 45);
		} else if ($y_grid == $grid_height - 1) {
			imagecopy($canvas, $frame_bottom, $x_offset, $y_offset, 0, 0, 45, 45);
		} else {
			imagecopy($canvas, $frame_center, $x_offset, $y_offset, 0, 0, 45, 45);
		}
	}
	imagedestroy($im);
	$x_grid += 1;
	if ($x_grid >= $grid_width) {
		$y_grid += 1;
		$x_grid = 0;
	}
}
$result -> close();
if ($view_mode == 0) {
	$imagefile = "images_new/blank.jpg";
	$im = imagecreatefromjpeg($imagefile);
	while ($x_grid < $grid_width) {
		$x_offset = $x_grid * 45;
		$y_offset = $y_grid * 45;
		imagecopy($canvas, $im, $x_offset, $y_offset, 0, 0, 45, 45);
		if ($x_grid == 0) {
			if ($y_grid == 0) {
				imagecopy($canvas, $frame_topleft, $x_offset, $y_offset, 0, 0, 45, 45);
			} else if ($y_grid == $grid_height - 1) {
				imagecopy($canvas, $frame_bottomleft, $x_offset, $y_offset, 0, 0, 45, 45);
			} else {
				imagecopy($canvas, $frame_left, $x_offset, $y_offset, 0, 0, 45, 45);
			}
		} else if ($x_grid == $grid_width - 1) {
			if ($y_grid == 0) {
				imagecopy($canvas, $frame_topright, $x_offset, $y_offset, 0, 0, 45, 45);
			} else if ($y_grid == $grid_height - 1) {
				imagecopy($canvas, $frame_bottomright, $x_offset, $y_offset, 0, 0, 45, 45);
			} else {
				imagecopy($canvas, $frame_right, $x_offset, $y_offset, 0, 0, 45, 45);
			}
		} else {
			if ($y_grid == 0) {
				imagecopy($canvas, $frame_top, $x_offset, $y_offset, 0, 0, 45, 45);
			} else if ($y_grid == $grid_height - 1) {
				imagecopy($canvas, $frame_bottom, $x_offset, $y_offset, 0, 0, 45, 45);
			} else {
				imagecopy($canvas, $frame_center, $x_offset, $y_offset, 0, 0, 45, 45);
			}
		}
		$x_grid = $x_grid + 1;
	}
	imagedestroy($im);
} else {
	$x_grid = $x_grid - 1;
	if ($x_grid == 0) {
		$x_offset = 0;
		$y_offset = $y_grid * 45;
		imagecopy($canvas, $frame_leftbottomright, $x_offset, $y_offset, 0, 0, 45, 45);
	} else if ($x_grid < $grid_width - 1) {
		$x_offset = $x_grid * 45;
		$y_offset = $y_grid * 45;
		imagecopy($canvas, $frame_bottomright, $x_offset, $y_offset, 0, 0, 45, 45);
	}
}
imagedestroy($frame_topleft);
imagedestroy($frame_top);
imagedestroy($frame_topright);
imagedestroy($frame_left);
imagedestroy($frame_center);
imagedestroy($frame_right);
imagedestroy($frame_bottomleft);
imagedestroy($frame_bottom);
imagedestroy($frame_bottomright);
imagedestroy($frame_leftbottomright);
$mysqli -> close();
//Header e output
header('Content-type: image/png');
imagepng($canvas);
imagedestroy($canvas);
?>