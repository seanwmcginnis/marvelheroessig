<?php
require_once('marvelheroes_classes.php');
require_once('marvelheroes_config.php');
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, "marvelheroesdb");
// Load the heroes
$query = "SELECT * FROM character_images ORDER BY display_position, costume_index";
$result = $mysqli->query($query);
$characters = array();


// This puts the heroes in an array -- old hat by now.
while ($myrow = $result->fetch_assoc()) {
    $char = NULL;
    $char_index = strval($myrow['character_index']);
    $found = false;
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
    if (!$found) {
        $char = new MarvelHero();
        $char->set_char_index(strval($myrow['character_index']));
        $char->set_char_name($myrow['character_name']);
        $char->push_cos_indices(strval($myrow['costume_index']));
        $char->push_cos_images("images_new/" . $myrow['image_file']);
        $char->push_cos_names($myrow['costume_name']);
        array_push($characters, $char);
    }
}
$result->close();
$mysqli->close();

if (!empty($_POST)) {
    generateSig(true, $characters);
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
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

            function init()
            {
                var markdown_el = document.getElementById('hidden_markdown');
                if (markdown_el)
                {
                    var markdown = markdown_el.value;
                    if (markdown && markdown.length > 0)
                    {
                        document.getElementById('markdown').value = markdown;
                    }
                }
            }
        </script>
    </head>
    <body onload="init()">
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

        function customError($errno, $errstr) {
            echo "<b>Error:</b> [$errno] $errstr<br>";
            echo "Ending Script<br>";
            echo "Host: " . DB_HOST . "<br>";
            var_dump(debug_backtrace());
            print_r($characters);
            die();
        }

        // set_error_handler("customError");

        if (!empty($_POST['tagSubmit'])) {
            generateSig(false, $characters);
        }

        if (empty($_POST['position_grid']) && empty($_COOKIE["marvelsig_position_grid"]) && !empty($_COOKIE["marvelsig_config"])) {
            convertLegacyCookies($characters);
        }

        $level_grid = $_POST['hidden_level_grid'];
        if (empty($level_grid) || strlen($level_grid) <= 0) {
            $level_grid = $_COOKIE['marvelsig_level_grid'];
        }

        $position_grid = $_POST['hidden_position_grid'];
        if (empty($position_grid) || strlen($position_grid) <= 0) {
            $position_grid = $_COOKIE['marvelsig_position_grid'];
        }

        $costume_grid = $_POST['hidden_costume_grid'];
        if (empty($costume_grid) || strlen($costume_grid) <= 0) {
            $costume_grid = $_COOKIE['marvelsig_costume_grid'];
        }


        populateCostumes($characters, $level_grid, $costume_grid, $position_grid);

        function logvisit($mysqli) {
            
        }

        function showAlert($message) {
            print "\n\n <script language='javascript'> \n\n alert('$message'); \n\n </script>\n\n";
        }

        function clearCookie($key) {
            if (isset($_COOKIE[$key])) {
                unset($_COOKIE[$key]);
                setcookie($key, '', time() - 3600);
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

// V1 stored a bunch of stuff in cookies.  We need to convert those to the new model.
        // This is an almost-exact duplicate of the same code from the renderer
        function convertLegacyCookies($characters) {
            $num_characters = count($characters);
            // Grab the config
            $config_cookie = $_COOKIE["marvelsig_config"];
            $new_level_grid = "";
            // Allocate some default grids
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

            // Go through all of the characters and build the strings; I'm not going to belabor it, since this code also exists to a degree in the client.
            for ($i = 0; $i < $num_characters; $i++) {
                $char_index = $characters[$i]->get_char_index();
                $display_order = 0;

                if ($config_cookie != NULL && strlen($config_cookie) > (5 * intval($char_index))) {
                    $level = substr($config_cookie, (5 * intval($char_index)) + 2, 3);
                    $new_level_grid = setGridValue($new_level_grid, str_pad($level, 3, "0", STR_PAD_LEFT), intval($char_index), 3);
                } else {
                    $new_level_grid = $new_level_grid . "000";
                    $new_level_grid = setGridValue($new_level_grid, "000", intval($char_index), 3);
                }

                $grid_tag = "X-1Y-1";
                if ($config_cookie != NULL && strlen($config_cookie) > (5 * intval($char_index))) {
                    $cos = substr($config_cookie, 5 * intval($char_index), 2);
                    if (intval($cos) != 99) {
                        $new_costume_grid = setGridValue($new_costume_grid, str_pad($cos, 2, "0", STR_PAD_LEFT), intval($char_index), 2);
                        $grid_tag = "X" . str_pad($x, 2, "0", STR_PAD_LEFT) . "Y" . str_pad($y, 2, "0", STR_PAD_LEFT);
                        $x += 1;
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
            // Set the hidden fields with the new values
            $_POST['hidden_cols'] = "14";
            $_POST['hidden_rows'] = strval($y + 1);
            $_POST['position_grid'] = $new_position_grid;
            $_POST['costume_grid'] = $new_costume_grid;
            $_POST['level_grid'] = $new_level_grid;
        }

        function populateCostumes($characters, $level_grid, $costume_grid, $position_grid) {
            print "<form method='post' action='configuresig_old.php'>\n";
            print "<center><table class='DataTable' cellpadding='8'>";
            print "<thead><tr><td>Character</td><td>Costume</td><td>Level</td></tr></thead><tbody>";
            foreach ($characters as $character) {
                $char_index = $character->get_char_index();
                $char_name = $character->get_char_name();
                print "<tr><td>$char_name</td>";
                $costume_name = "cos_" . $char_index;
                $level_name = "lev_" . $char_index;
                $pos = getGridValue($position_grid, $char_index, 6);
                $do_not_display = false;
                if ($pos == "X-1Y-1") {
                    $do_not_display = true;
                }
                $selected_costume_index = getGridValue($costume_grid, $char_index, 2);
                print "<td><select name='$costume_name'>";
                for ($i = 0; $i < $character->get_cos_names_count(); $i++) {
                    $cos_index = $character->pop_cos_indices($i);
                    $cos_name = $character->pop_cos_names($i);
                    print "<option value='$cos_index'";
                    if (intval($cos_index) == intval($selected_costume_index) && !$donotdisplay) {
                        print " selected";
                    }
                    print ">$cos_name</option>";
                }
                print "<option value='99'";
                if ($do_not_display) {
                    print " selected";
                }
                print ">Do not display</option>";
                print "</select></td><td><input type='text' name='$level_name'";
                $level = getGridValue($level_grid, $character->get_char_index(), 3);
                if (intval($level) > 0) {
                    print " value='$level'";
                }
                print "></td></tr>";
            }
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
                $chars_per_row = "14";
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
            $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, "marvelheroesdb");
            print "<tr><td>Font</td><td><select name='font'>";
            $query = "SELECT * FROM fonts ORDER BY font_name";
            $result = $mysqli->query($query);
            while ($myrow = $result->fetch_assoc()) {
                $font_index = intval($myrow['font_index']);
                $font_name = $myrow['font_name'];
                print "<option value='$font_index'";
                if ($font_index == $font) {
                    print " selected";
                }
                print ">$font_name</option>";
            }
            $result->close();
            print "</select></td></tr>";

            // KEYWORD
            print "<tr><td>Keyword</td><td><input type='text' name='saved_keyword' value='$keyword'></td></tr>";
            print "<tr><td>Password</td><td><input type='text' name='saved_password' value=''></td></tr>";
            print "</tbody></table><br>";
            print "<center><input type='submit' name='tagSubmit' value='Generate Sig'>";
            print "<input type='hidden' name='hidden_costume_grid' value='$costume_grid'>";
            print "<input type='hidden' name='hidden_level_grid' value='$level_grid'>";
            print "<input type='hidden' name='hidden_position_grid' value='$position_grid'>";
            print "</center></form><br><br>";

            $val = "";
            if (strlen($position_grid) > 0 && strlen($level_grid) > 0 && strlen($costume_grid) > 0) {
                $val = "http://www.seanwmcginnis.com/marvelheroes/generatesig.php?position_grid=" . $position_grid . "&level_grid=" . $level_grid . "&costume_grid=" . $costume_grid . "&version=2&view_mode=" . strval($view_mode) . "&grid_width=" . strval($chars_per_row) . "&font=" . strval($font);
                print "<center><div><img src='$val'></div></center>";
            }
            print "<br><p>Markdown (put this in your signature on the forums):</p>";
            print "<textarea id='markdown' rows='4' cols='100'></textarea>";
            $mysqli->close();
        }

        function generateSig($fromheader, $characters) {
            $counter = 0;
            $costume_name = "cos_" . strval($counter);
            $level_name = "lev_" . strval($counter);
            $chars_per_row = 0;
            $view_mode = 0;
            $include_link = 1;
            $font = 0;
            $position_grid = "";
            $level_grid = "";
            $costume_grid = "";
            $include_tooltips = 0;

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

            $column = 0;
            $row = 0;
            foreach ($characters as $character) {
                $grid_val = "X" . str_pad($column, 2, "0", STR_PAD_LEFT) . "Y" . str_pad($row, 2, "0", STR_PAD_LEFT);
                $position_grid = $position_grid . $grid_val;
                $level_grid = $level_grid . "000";
                $costume_grid = $costume_grid . "00";
                $column += 1;
                if ($column >= $chars_per_row) {
                    $column = 0;
                    $row += 1;
                }
            }

            $column = 0;
            $row = 0;
            foreach ($characters as $character) {
                $char_index = $character->get_char_index();
                $costume_name = "cos_" . strval($char_index);
                $level_name = "lev_" . strval($char_index);
                if (isset($_POST[$costume_name])) {
                    if (intval($_POST[$costume_name]) == 99) {
                        $costume_grid = setGridValue($costume_grid, $_POST[$costume_name], $char_index, 2);
                        $position_grid = setGridValue($position_grid, "X-1Y-1", $char_index, 6);
                    } else {
                        $costume_grid = setGridValue($costume_grid, $_POST[$costume_name], $char_index, 2);
                        $grid_val = "X" . str_pad($column, 2, "0", STR_PAD_LEFT) . "Y" . str_pad($row, 2, "0", STR_PAD_LEFT);
                        $position_grid = setGridValue($position_grid, $grid_val, $char_index, 6);
                        $column += 1;
                        if ($column >= $chars_per_row) {
                            $column = 0;
                            $row += 1;
                        }
                    }
                }
                if (isset($_POST[$level_name])) {
                    $level_grid = setGridValue($level_grid, $_POST[$level_name], $char_index, 3);
                }
            }

            if ($fromheader) {
                setcookie("marvelsig_position_grid", $position_grid, time() + (60 * 60 * 24 * 30));
                setcookie("marvelsig_level_grid", $level_grid, time() + (60 * 60 * 24 * 30));
                setcookie("marvelsig_costume_grid", $costume_grid, time() + (60 * 60 * 24 * 30));

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

            $_POST['hidden_position_grid'] = $position_grid;
            $_POST['hidden_level_grid'] = $level_grid;
            $_POST['hidden_costume_grid'] = $costume_grid;
            $markdown = "";
            if (strlen($position_grid) > 0 && strlen($level_grid) > 0 && strlen($costume_grid) > 0) {
                $url = "http://www.seanwmcginnis.com/marvelheroes/generatesig.php?position_grid=" . $position_grid . "&level_grid=" . $level_grid . "&costume_grid=" . $costume_grid . "&version=2&view_mode=" . strval($view_mode) . "&grid_width=" . strval($chars_per_row) . "&font=" . strval($font);
                $client_id = IMGUR_CLIENTID;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image.json');
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Client-ID e583384de73c9c9'));
                curl_setopt($ch, CURLOPT_POSTFIELDS, array('image' => $url));

                $reply = curl_exec($ch);
                $error = curl_error($ch);
                curl_close($ch);
                if (strlen($error) > 0) {
                    $ret = array();
                    $ret["error"] = $error;
                    $ret["val"] = $reply;
                    $ret["location"] = "UPLOAD";
                    $markdown = "There was an error: " . json_encode($ret);
                } else {
                    $obj = json_decode($reply);
                    $imgur = 'https://i.imgur.com/' . $obj->data->id . '.png';
                    $markdown = "![]($imgur)";
                    if ($include_link === 1) {
                        $link_text = "[**Hero Roster 2.x**](https://forums.marvelheroes.com/discussion/43655/hero-roster-2-x)";
                        $markdown = $markdown . "\n\n" . $link_text;
                    }
                }
            }
            print "<input type='hidden' name='hidden_markdown' id='hidden_markdown' value='$markdown'>";
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
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("s", $keyword);
            $stmt->execute();
            $stmt->bind_result($saved_password);
            $stmt->fetch();

            $can_save = true;
            if (empty($saved_password)) {
                $can_save = true;
            } else if ($saved_password != $password) {
                $can_save = false;
            }
            $stmt->close();

            $version = 2;

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

                $default_flair_grid = "000";
                for ($i = 1; $i < 4 * count($characters); $i++) {
                    $default_flair_grid = $default_flair_grid . "000";
                }
                $default_border_type = 0;
                $default_border_color = "00aaff";
                $default_half_grids = "";
                $query = "INSERT INTO saved_config(keyword, password, position_grid, costume_grid, level_grid, view_mode, font, include_link, include_tooltips, border_type, flair_grid, border_color, version, half_grids) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("sssssiiiiissis", $keyword, $password, $position_grid, $costume_grid, $level_grid, $view_mode, $font, $include_link, $include_tooltips, $default_border_type, $default_flair_grid, $default_border_color, $version, $default_half_grids);
                $stmt->execute();
                $stmt->close();

                if (!$fromHeader) {
                    showAlert("Sig saved!  Make sure you get the revised markdown, with your keyword included in the link!");
                }
            } else {
                if (!$fromHeader) {
                    showAlert("Your password is incorrect, or that keyword is already taken.  Please try again.");
                }
            }
        }
        ?>
    </body>
</html>