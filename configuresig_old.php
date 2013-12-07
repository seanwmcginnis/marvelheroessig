<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<?php
if (!empty($_POST)) {
	generateSig(true);
}
?>

<html>
	<head>
		<title>Configure Marvel Heroes Custom Sig</title>
		<link rel="stylesheet" href="default_old.css" type="text/css">
		<meta http-equiv="Content-Type" content="text/html; ">
		<script type="text/javascript" language="JavaScript">
			function toggleDiv(divname) {
				var el = document.getElementById(divname);
				if (el) {
					if (el.style["display"] == "none") {
						el.style["display"] = "";
					} else {
						el.style["display"] = "none";
					}
				}
			}
		</script>
	</head>
	<body>
		<center>

			<h1>Configure Marvel Heroes Custom Sig</h1>
		</center>
		<br>
		<div style='margin-left: auto; margin-right: auto; width:600px;'>
			<p>
				Welcome to my (admittedly crude) tool for generating a custom roster sig for Marvel Heroes.
			</p>
			<a href="javascript:toggleDiv('manual_basic')">Instructions Part 1: The Basics</a>
			<br>
			<div id='manual_basic' style="display:none">
				<ul>
					<li>
						Step 1: For each hero that you own, go through the list and select their costume from the dropdown in the left column.
					</li>
					<li>
						Step 1a: If you want the hero to be grayed-out, select "Unavailable" (the first item in the dropdown).  If you do not want the hero to show up at all, select "Do Not Display" (the last item in the dropdown).
					</li>
					<li>
						Step 2: For each hero that you own, enter the current level of that hero in the text box in the right column as an integer.  If you do not currently own a hero, leave the box blank.  If you enter words or random punctuation, something will probably explode -- that's on you.
					</li>
					<li>
						Step 2a: For heroes that are using the prestige system (prestiging?), enter their level as the cumulative number of levels they have acquired.  So if you are level 5 with a green name, enter "65".  If you are level 22 with a purple name, enter "202" (I think).
					</li>
					<li>
						Step 3: If you want to use some of the snazzy option, look at the next section of the manual.  If you want the bare basics, leave the other controls alone.
					</li>
					<li>
						Step 4: Press the "Generate Sig" button.  Your sig should show up at the bottom of the page.  More importantly, some markdown should show up in the textbox labeled "Markdown".
					</li>
					<li>
						Step 5: Copy the markdown from the textbox at the bottom, and put it in your forum sig.  Either select all of the text in the white box with your mouse like a neanderthal, or click in the box and hit "Ctrl-A".  Then you can either right-click and select "Copy" (making you little better than an ape), or hit "Ctrl-C".
						<br>
						<img src='images_new/manual_4.jpg' alt='Keyboard shortcuts'/>
					</li>
					<li>
						Step 6: To do this, first click on the little gear beneath your name on the forums.
						<br>
						<img src='images_new/manual_1.jpg' alt='This is a helpful screenshot.'/>
					</li>
					<li>
						Step 7: Select "Edit Profile" from the menu.
						<br>
						<img src='images_new/manual_2.jpg' alt='This is a helpful screenshot.'/>
					</li>
					<li>
						Step 8: Select "Signature Settings" from the menu on the left side of the screen.
						<br>
						<img src='images_new/manual_3.jpg' alt='This is a helpful screenshot.'/>
					</li>
					<li>
						Step 9: Delete whatever inferior sig was in the "Signature Code" previously by select all of the text and hitting delete.
						<br>
						<img src='images_new/manual_5.jpg' alt='This is a helpful screenshot.'/>
					</li>
					<li>
						Step 10: Paste your new, glorious sig code into the box.  Then click "Save".
						<br>
						<img src='images_new/manual_6.jpg' alt='This is a helpful screenshot.'/>
					</li>
					<li>
						Step 11: Post on the forums, comfortable in the knowledge that all who read your post will know the exact level and appearance of your entire roster.  Revel in this feeling!
					</li>
					<li>
						Step 12: If cookies are enabled, your data should be saved; this will make it less painful to update your sig when you level up, though you will still need to copy the new markdown to the Forums.  If you want to make it super-less-painful, read "Instructions Part 3".
					</li>
				</ul>
			</div>
			<a href="javascript:toggleDiv('manual_advanced')">Instructions Part 2: Advanced Configuration</a>
			<br>
			<div id='manual_advanced' style="display:none">
				<p>
					Below the roster is a "Settings" section that I told you not to look at in Part 1.  You may now look at it.  There.  Okay, that's enough...look away.  Fill out these options and then click "Generate Sig" -- the preview and the markdown will change to reflect your settings.
				</p>
				<p>
					You can do the following things in this section:
				</p>
				<ul>
					<li>
						Thing 1: <i>Set the number of characters per row.</i> By default (currently), this tool will produce a sig with two rows -- half of the displayed characters on top, and half on bottom.  You can override this behavior and make it put as many characters per row as you want (as long as it is greater than 1).  Type in the number of characters you want per row in the textbox.
					</li>
					<li>
						Thing 2: <i>Change the shape of your sig.</i> If the last row comes up with fewer characters than the preceding rows, you have two options.  The default is "Sig should always be square".  This will fill in the unoccupied spaces with black, like so:
						<br>
						<img src='images_new/manual_7.jpg' alt='This is a helpful screenshot.'/>
						<br>
						If you select "Sig can be odd shaped", the row will just be clipped off, and the rest of the image will be transparent:
						<br>
						<img src='images_new/manual_8.jpg' alt='This is a helpful screenshot.'/>
					</li>
					<li>
						Thing 3: <i>Promote this site.</i> No doubt, people will ask "Hey, where did you get that majestic sig?"  When you grow weary of answering this question, enabled the "Include a link in my sig" option to place a link to the message board thread in your sig.  You will have to update the markdown (see Part 1) to complete this change.
					</li>
					<li>
						Thing 4: <i>Change the font.</i> The default font is terrible.  You can select one of a number of heroic fonts on this option.  This will change the font that your level numbers are rendered with.
					</li>
					<li>
						Thing 5: <i>Set a keyword and password.</i> This is the ultimate is sig luxury; look at the next section to get details.
					</li>
				</ul>
			</div>
			<a href="javascript:toggleDiv('manual_keyword')">Instructions Part 3: Saving your sig with a keyword</a>
			<br>
			<div id='manual_keyword' style="display:none">
				<p>
					By default, this page generates markdown that puts all of the sig information in the URL of the image.  That means that each time you change your sig, you need to update your sig on the Forums.  If you get sick of doing this, you can store your signature config in my database, and refer to it with a keyword.  Then, whenever you make changes to your sig on this site, those changes are <i>automagically</i> reflected on the forums.  To do this:
					<ul>
						<li>
							Step 1: Type a keyword into the "Keyword" textbox.  This is like a username -- a unique identifier that is linked to your keyword.  If you enter one that is not unique, you will need to select a different keyword (or guess the other person's password, at which point you can overwrite their sig -- you varlet, you).
						</li>
						<li>
							Step 2: Type in a password.  This isn't your credit card information, and you will notice that this site is not secure.  Don't user a username and password that is linked to any other accounts -- I'm salting and hashing the passwords, but I don't want it on me if your car gets repossessed.
						</li>
						<li>
							Step 3: Click the "Generate Sig" button.  The sig is generated.  Notice that the markdown is significantly shorter (unless your keyword is, like, 200 characters long).
						</li>
						<li>
							Step 4: Update your sig on the forums, as per Part 1.
						</li>
						<li>
							Step 5: When you level up, come here and update your sig.  You will need to enter the same keyword and password, but you will notice that your sig automatically changes.
						</li>
						<li>
							Step 6: IF YOU FORGET YOUR PASSWORD...well, tough.  Pick a new keyword and re-update your sig.  Since you enabled cookies (you enabled cookies, right?), you just need to update your sig on the forums with the new keyword.  This ain't your office and I ain't your network admin.
						</li>
					</ul>
				</p>
			</div>
			<p>
				That's it!  Let me know of any problems via the forums. <a href='https://forums.marvelheroes.com/discussion/43655/hero-roster-2-x'>This thread</a> is the best place to post bug reports, feature requests, and lavish praise.
			</p>
			<p>
				<strong>Important: Credit to zztodd on the forums for the images; I only wrote this code and the sig-generating code, he has done all of the hard work.</strong>
			</p>
		</div>

		<?php

	include 'marvelheroes_config.php';

	function customError($errno, $errstr) {
		echo "<b>Error:</b> [$errno] $errstr<br>";
		echo "Ending Script<br>";
		echo "Host: " . DB_HOST . "<br>";
		var_dump(debug_backtrace());
		print_r($characters);
		die();
	}

	set_error_handler("customError");

	if (!empty($_POST['tagSubmit'])) {
		generateSig(false);
	}
	populateCostumes();

	function logvisit($mysqli) {
		$ip_address = $_SERVER['REMOTE_ADDR'];
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$referrer = "";
		if (!empty($_SERVER['HTTP_REFERER'])) {
			$referrer = $_SERVER['HTTP_REFERER'];
		}
		$request_uri = $_SERVER['REQUEST_URI'];

		$query = "INSERT INTO visitors(ip_address, user_agent, referrer, target_url) VALUES('$ip_address', '$user_agent', '$referrer', '$request_uri')";
		$result = $mysqli -> query($query);
	}

	function showAlert($message) {
		print "\n\n <script language='javascript'> \n\n alert('$message'); \n\n </script>\n\n";
	}

	function clearCookie($key) {
		if (isset($_COOKIE[$key])) {
			unset($_COOKIE[$key]);
			setcookie('key', '', time() - 3600);
			// empty value and old timestamp
		}
	}

	function getPostVariable($intag) {
		if (empty($_POST[$intag])) {
			return null;
		}
		return $_POST[$intag];
	}

	function getCookieVariable($intag) {
		if (empty($_COOKIE[$intag])) {
			return null;
		}
		return $_COOKIE[$intag];
	}

	function populateCostumes() {
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, "marvelheroesdb");
		logvisit($mysqli);
		$query = "SELECT * FROM character_images ORDER BY display_position, costume_index";
		$result = $mysqli -> query($query);
		print "<form method='post' action='configuresig_old.php'>\n";
		print "<center><table class='DataTable' cellpadding='8'>";
		print "<thead><tr><td>Character</td><td>Costume</td><td>Level</td></tr></thead><tbody>";
		$last_char = "";
		$last_char_index = "";
		$char_index = "";
		$costume_name = "";

		$config_cookie = NULL;
		$config_cookie = getPostVariable('hidden_configarray');
		if ($config_cookie == NULL || strlen($config_cookie) <= 0) {
			$config_cookie = getCookieVariable("marvelsig_config");
		}
		while ($myrow = $result -> fetch_assoc()) {
			$character = $myrow['character_name'];
			$char_index = strval($myrow['character_index']);
			if ($character != $last_char) {
				if ($last_char != "") {
					print "<option value='99'";
					if ($config_cookie != NULL && strlen($config_cookie) > (5 * intval($last_char_index))) {
						$cos = substr($config_cookie, 5 * intval($last_char_index), 2);
						if (intval($cos) == 99) {
							print " selected";
						}
					}
					print ">Do not display</option>";
					$level_name = "lev_" . $last_char_index;
					print "</select></td><td><input type='text' name='$level_name'";
					if ($config_cookie != NULL && strlen($config_cookie) > (5 * intval($last_char_index))) {
						$level = substr($config_cookie, (5 * intval($last_char_index)) + 2, 3);
						if (intval($level) > 0) {
							print " value='$level'";
						}
					}
					print "></td></tr>";
				}
				print "<tr><td>$character</td>";
				$costume_name = "cos_" . $char_index;
				print "<td><select name='$costume_name'>";
			}
			$last_char = $character;
			$last_char_index = $char_index;
			$cos_index = strval($myrow['costume_index']);
			$cos_name = $myrow['costume_name'];
			print "<option value='$cos_index'";
			if ($config_cookie != NULL && strlen($config_cookie) > 0) {
				if (strlen($config_cookie) > (5 * intval($char_index))) {
					$cos = substr($config_cookie, 5 * intval($char_index), 2);
				} else {
					$cos = "99";
				}
				if (intval($cos) == intval($cos_index)) {
					print " selected";
				}

			}
			print ">$cos_name</option>";
		}
		$result -> close();
		print "<option value='99'";
		if ($config_cookie != NULL && strlen($config_cookie) > (5 * intval($char_index))) {
			$cos = substr($config_cookie, 5 * intval($char_index), 2);
			if (intval($cos) == 99) {
				print " selected";
			}
		}
		print ">Do not display</option>";
		$level_name = "lev_" . $char_index;
		print "</select></td><td><input type='text' name='$level_name'";
		if ($config_cookie != NULL && strlen($config_cookie) > (5 * intval($char_index))) {
			$level = substr($config_cookie, (5 * intval($char_index)) + 2, 3);
			if (intval($level) > 0) {
				print " value='$level'";
			}
		}
		print "></td></tr>";
		print "</tbody></table></center><br><br><br>";
		print "<center><table class='DataTable' cellpadding='8'><thead><tr><td colspan='2'>Settings</td></tr></thead><tbody>";
		$keyword = getPostVariable('saved_keyword');
		if ($keyword == NULL) {
			$keyword = getCookieVariable("marvelsig_keyword");
		}
		if ($keyword == NULL) {
			$keyword = "";
		}
		$chars_per_row = getPostVariable('chars_per_row');
		if ($chars_per_row == NULL) {
			$chars_per_row = getCookieVariable("marvelsig_chars_per_row");
		}
		if ($chars_per_row == NULL) {
			$chars_per_row = "0";
		}
		$view_mode = getPostVariable('view_mode');
		if ($view_mode == NULL) {
			$view_mode = getCookieVariable("marvelsig_view_mode");
		}
		if ($view_mode == NULL) {
			$view_mode = 0;
		}
		$include_link = getPostVariable('include_link');
		if ($include_link == NULL) {
			$include_link = getCookieVariable("marvelsig_include_link");
		}
		if ($include_link == NULL) {
			$include_link = 1;
		}
		$font = getPostVariable('font');
		if (!empty($font)) {
			$font = intval(getPostVariable('font'));
		}
		if ($font == NULL) {
			$font = intval(getCookieVariable("marvelsig_font"));
		}
		if ($font == NULL) {
			$font = 0;
		}

		// CHARACTERS PER ROW
		print "<tr><td>Characters per row</td><td><input type='text' name='chars_per_row' value='$chars_per_row'></td></tr>";

		// SIG SHAPE
		print "<tr><td>Shape</td><td><select name='view_mode'>";
		print "<option value='0'";
		if ($view_mode == 0) {
			print " selected";
		}
		print ">Sig should always be square</option>";
		print "<option value='1'";
		if ($view_mode == 1) {
			print " selected";
		}
		print ">Sig can be odd shaped</option>";
		print "</select></td></tr>";

		// INCLUDE LINK
		print "<tr><td>Link</td><td><select name='include_link'>";
		print "<option value='0'";
		if ($include_link == 0) {
			print " selected";
		}
		print ">Do not include a link in my sig</option>";
		print "<option value='1'";
		if ($include_link == 1) {
			print " selected";
		}
		print ">Include a link in my sig</option>";
		print "</select></td></tr>";

		// FONTS
		print "<tr><td>Font</td><td><select name='font'>";
		$query = "SELECT * FROM fonts ORDER BY font_name";
		$result = $mysqli -> query($query);
		while ($myrow = $result -> fetch_assoc()) {
			$font_index = intval($myrow['font_index']);
			$font_name = $myrow['font_name'];
			print "<option value='$font_index'";
			if ($font_index == $font) {
				print " selected";
			}
			print ">$font_name</option>";
		}
		$result -> close();
		print "</select></td></tr>";

		// KEYWORD
		print "<tr><td>Keyword</td><td><input type='text' name='saved_keyword' value='$keyword'></td></tr>";
		print "<tr><td>Password</td><td><input type='text' name='saved_password' value=''></td></tr>";
		print "</tbody></table><br>";
		print "<center><input type='submit' name='tagSubmit' value='Generate Sig'><input type='hidden' name='hidden_configarray' value='$config_cookie'>";
		print "</center></form><br><br>";

		if ($config_cookie != NULL) {
			$val = "http://www.seanwmcginnis.com/marvelheroes/marvelsig.php?config=" . $config_cookie . "&view_mode=" . strval($view_mode) . "&grid_width=" . strval($chars_per_row) . "&font=" . strval($font);
			print "<center><div><img src='$val'></div></center>";
		}
		if ($keyword != NULL && strlen($keyword) > 0) {
			$val = "http://www.seanwmcginnis.com/marvelheroes/marvelsig.php?keyword=" . $keyword;
		} else {
			$val = "http://www.seanwmcginnis.com/marvelheroes/marvelsig.php?config=" . $config_cookie . "&view_mode=" . strval($view_mode) . "&grid_width=" . strval($chars_per_row) . "&font=" . strval($font);
		}
		$markdown = "![]($val)";
		if ($include_link == 1) {
			$link_text = "[**Hero Roster 2.x**](https://forums.marvelheroes.com/discussion/43655/hero-roster-2-x)";
			$markdown = $markdown . "\n\n" . $link_text;
		}
		print "<br><p>Markdown (put this in your signature on the forums):</p>";
		print "<textarea rows='4' cols='100'>$markdown</textarea>";
		$mysqli -> close();
	}

	function generateSig($fromheader) {
		$counter = 0;
		$costume_name = "cos_" . strval($counter);
		$level_name = "lev_" . strval($counter);
		$config_array = "";
		$chars_per_row = 0;
		$view_mode = 0;
		$include_link = 1;
		$font = 0;
		while (!empty($_POST[$costume_name])) {
			$token = str_pad($_POST[$costume_name], 2, "0", STR_PAD_LEFT) . substr(str_pad($_POST[$level_name], 3, "0", STR_PAD_LEFT), 0, 3);
			$config_array = $config_array . $token;
			$counter = $counter + 1;
			$costume_name = "cos_" . strval($counter);
			$level_name = "lev_" . strval($counter);
		}
		if (!empty($_POST['chars_per_row'])) {
			$chars_per_row = intval($_POST['chars_per_row']);
		}
		if (!empty($_POST['view_mode'])) {
			$view_mode = intval($_POST['view_mode']);
		}
		if (!empty($_POST['include_link'])) {
			$include_link = intval($_POST['include_link']);
		}
		if (!empty($_POST['font'])) {
			$font = intval($_POST['font']);
		}
		if ($fromheader) {
			setcookie("marvelsig_config", $config_array, time() + (60 * 60 * 24 * 30));
			$keyword = $_POST['saved_keyword'];
			if ($keyword != NULL && strlen($keyword) > 0) {
				setcookie("marvelsig_keyword", $keyword, time() + (60 * 60 * 24 * 30));
			} else {
				clearCookie("marvelsig_keyword");
			}
			if (!empty($chars_per_row)) {
				setcookie("marvelsig_chars_per_row", strval($chars_per_row), time() + (60 * 60 * 24 * 30));
			} else {
				clearCookie("marvelsig_chars_per_row");
			}
			if (!empty($view_mode)) {
				setcookie("marvelsig_view_mode", strval($view_mode), time() + (60 * 60 * 24 * 30));
			} else {
				clearCookie("marvelsig_view_mode");
			}
			if (!empty($include_link)) {
				setcookie("marvelsig_include_link", strval($include_link), time() + (60 * 60 * 24 * 30));
			} else {
				clearCookie("marvelsig_include_link");
			}
			if (!empty($font)) {
				setcookie("marvelsig_font", strval($font), time() + (60 * 60 * 24 * 30));
			} else {
				clearCookie("marvelsig_font");
			}
			return;
		}
		if (!$fromheader) {
			$_POST['hidden_configarray'] = $config_array;
		}
		$keyword = $_POST['saved_keyword'];
		if ($keyword == NULL || strlen($keyword) <= 0) {
			return;
		}
		$password = $_POST['saved_password'];
		if ($password == NULL || strlen($password) <= 0) {
			showAlert("You must enter a password to go with your config -- otherwise, anyone could change it!");
			return;
		}
		$password = hash("sha512", $password);

		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, "marvelheroesdb");
		$query = "SELECT password FROM saved_config WHERE keyword=?";
		$stmt = $mysqli -> prepare($query);
		$stmt -> bind_param("s", $keyword);
		$stmt -> execute();
		$stmt -> bind_result($saved_password);
		$stmt -> fetch();

		$can_save = true;
		if (empty($saved_password)) {
			$can_save = true;
		} else if ($saved_password != $password) {
			$can_save = false;
		}
		$stmt -> close();
		if ($can_save) {
			$query = "DELETE FROM saved_config WHERE keyword=?";
			$stmt = $mysqli -> prepare($query);
			$stmt -> bind_param("s", $keyword);
			$stmt -> execute();
			$stmt -> close();

			$query = "INSERT INTO saved_config(keyword, password, config, chars_per_row, view_mode, include_link, font) VALUES (?,?,?,?,?,?,?)";
			$stmt = $mysqli -> prepare($query);
			$stmt -> bind_param("sssiiii", $keyword, $password, $config_array, $chars_per_row, $view_mode, $include_link, $font);
			$stmt -> execute();
			$stmt -> close();
		} else {
			if (!$fromheader) {
				showAlert("Your password is incorrect, or that keyword is already taken.  Please try again.");
				showAlert(strval($password));
			}
		}
	}
		?>
	</body>
</html>