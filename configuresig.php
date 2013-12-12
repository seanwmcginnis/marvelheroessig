<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<?php

/*
 *  Interactive Marvel Heroes Forum Signature Generator
 *
 Copyright 2013 Sean McGinnis
 http://www.seanwmcginnis.com/marvelheroes/configuresig.php

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

/*
 * configuresig.php: This is the module that configures the image.  This is a site that uses jQuery to do most of the work.
 */

// This bit is for setting cookies, which has to be done before the page renders.
include 'marvelheroes_config.php';

if (!empty($_POST["action"])) {
	if ($_POST["action"] == "save") {
		saveSig(true);
	}
	if ($_POST["action"] == "load") {
		loadSigFromDatabase();
	}
}
?>

<html>
	<head>
		<title>Configure Marvel Heroes Custom Sig</title>
		<link rel="stylesheet" href="default.css?version=2.3.1" type="text/css">
		<meta http-equiv="Content-Type" content="text/html; ">
		<script src="http://code.jquery.com/jquery-1.10.2.js"></script>
		<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
		<!-- <script src="jquery.ui.touch-punch.min.js"></script> -->
		<script src="marvelheroes_classes.js?version=2.3.1"></script>
		<script>
			// V1 was all PHP.  This one is mostly jQuery.  Good stuff -- a lot less load on the server, and a better end-user experience.
			// The general model is: the server puts character info in hidden fields.  This code loads it and does all of the rendering and whatnot.

			// The characters array stores info about the characters
			var characters = new Array();

			// The flair array stores info about the flair icons
			var flair = new Array();

			// saveSig had some validation in it at some point, but it posts back to the server
			// NOTE: the "action" field is used as a dirty flag.  It gets reset when the grids change.
			function saveSig() {
				$("#action").val("save");
				document.marvelform.submit();
			}

			// saveSig had some validation in it at some point, but it posts back to the server
			// NOTE: the "action" field is used as a dirty flag.  It gets reset when the grids change.
			function loadSigFromDatabase() {
				$("#action").val("load");
				document.marvelform.submit();
			}

			// This generates some fake guids for the image maps.  jQuery supposedly has some internal guid generation code, but it is not exposed (and may break stuff if you try to use it)
			function s4() {
				return Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
			};

			function guid() {
				return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
			}

			// This function builds the markdown code, which is actually no longer markdown, but rather straight HTML
			function buildMarkdown() {
				console.log("Action is: " + $("#action").val());
				// Check to see if the sig is dirty (that is, has been modified and not saved).  If the sig has been saved and not subsequently modified, then the "action" field will read "save".
				var save_by_keyword = $("#action").val() == "save";
				// Get the settings from the UI fields.  These setting are covered in the sig-generation code.
				var font = $("#font").val();
				var border_type = $("#border_type").val();
				var view_mode = $("#view_mode").val();
				var position_grid = $("#position_grid").val();
				var costume_grid = $("#costume_grid").val();
				var flair_grid = $("#flair_grid").val();
				var level_grid = $("#level_grid").val();
				var keyword = $("#saved_keyword").val();
				// Except these two.  include_link determines whether a link to my thread on the forums should be included in the markdown
				var include_link = $("#include_link").val();
				// Include tooltips indicates whether a tooltip image map should be generated, and what kind.
				var include_tooltips = $("#include_tooltips").val();
				var url = "";

				if (save_by_keyword && keyword.length > 0) {
					// If this was keyworded, then the only parameter is the keyword
					url = "http://www.seanwmcginnis.com/marvelheroes/marvelsig.php?keyword=" + keyword;
				} else {
					// Otherwise, all of the grids and parameters need to go into the URL
					url = "http://www.seanwmcginnis.com/marvelheroes/marvelsig.php?position_grid=" + position_grid + "&level_grid=" + level_grid + "&costume_grid=" + costume_grid + "&flair_grid=" + flair_grid;
					;
					if (font != null) {
						url = url + "&font=" + font.toString();
					}
					if (view_mode != null) {
						url = url + "&view_mode=" + view_mode.toString();
					}
					if (border_type != null) {
						url = url + "&border_type=" + border_type.toString();
					}
				}
				// Begin the markdown with the image itself
				var markdown = "<img src='" + url + "' alt='My super fantastic custom sig'";
				if (include_tooltips > 0) {
					// If we're doing tooltips, generate a unique name for the image map.
					var map_name = guid();
					markdown += " usemap='" + map_name + "'>";
					markdown += "<map name='" + map_name + "'>";
					var char_index = 0;
					var cos_index = 0;
					var coords = "";
					var grid_tag = "";
					var title = "";
					// Iterate through the character indices
					for ( char_index = 0; char_index < characters.length; char_index++) {
						// Get the location of the character
						grid_tag = getGridValue(position_grid, char_index, 6);
						cos_index = parseInt(getGridValue(costume_grid, char_index, 2), 10);
						// If the character is not on the grid, then skip
						if (grid_tag == null) {
							grid_tag = "X-1Y-1";
						}
						if (grid_tag != "X-1Y-1") {
							// Parse the coordinates
							var x = parseInt(grid_tag.substr(1, 2), 10);
							var y = parseInt(grid_tag.substr(4, 2), 10);
							// Put togehter a title, starting with the character's name.
							title = characters[char_index].char_name;
							// Find the index of the character's costume in the arrays, and pull it.
							for (var i = 0; i < characters[char_index].costume_indices.length; i++) {
								if (parseInt(characters[char_index].costume_indices[i], 10) == cos_index) {
									title = title + " (" + characters[char_index].costume_names[i] + ")";
									break;
								}
							}
							// If we are including the level add that to the title.
							if (include_tooltips == 1) {
								if (characters[char_index].level > 0) {
									title = title + ": Level " + characters[char_index].level.toString();
									if (characters[char_index].prestige == 1) {
										title = title + " Green";
									} else if (characters[char_index].prestige == 2) {
										title = title + " Blue";
									} else if (characters[char_index].prestige == 3) {
										title = title + " Purple";
									} else if (characters[char_index].prestige == 4) {
										title = title + " Orange";
									} else if (characters[char_index].prestige == 5) {
										title = title + " Red";
									}
								}
							}
							// Calculate the coords, which are just 45 * x and 45 * y
							coords = (45 * x).toString() + "," + (45 * y).toString() + "," + ((45 * x) + 45).toString() + "," + ((45 * y) + 45).toString();
							// Add the final map entry to the markdown
							markdown += "<area shape='rect' coords='" + coords + "' title='" + title + "'>";
						}
					}
					// Close the map
					markdown += "</map>";
				} else {
					markdown = markdown + ">";
				}
				var link_text = "<a href='https://forums.marvelheroes.com/discussion/43655/hero-roster-2-x'>**Hero Roster 2.x**</a>";
				// Add the link, if desired
				if (include_link == 1) {
					markdown = markdown + "\n\n" + link_text;
				}
				$("#markdown").val(markdown);
			}

			// Similar to the above, this generates the preview -- which just passes the configuration to the sig code.
			function previewSig() {
				// Get the parameters from the UI
				var font = $("#font").val();
				var view_mode = $("#view_mode").val();
				var position_grid = $("#position_grid").val();
				var costume_grid = $("#costume_grid").val();
				var flair_grid = $("#flair_grid").val();
				var level_grid = $("#level_grid").val();
				var border_type = $("#border_type").val();

				// Build the URL.
				var url = "marvelsig.php?position_grid=" + position_grid + "&level_grid=" + level_grid + "&costume_grid=" + costume_grid + "&flair_grid=" + flair_grid;
				if (font != null) {
					url = url + "&font=" + font.toString();
				}
				if (view_mode != null) {
					url = url + "&view_mode=" + view_mode.toString();
				}
				if (border_type != null) {
					url = url + "&border_type=" + border_type.toString();
				}
				// This is the title; I was too lazy to make a div-inside-a-div for this.
				var title = "<div class='titleclause'>Preview</div>";
				// Put the image in the div.
				$("#preview").html(title + "<center><img src='" + url + "'></center>");
				buildMarkdown();
				positionElements();
			}

			// This function writes the character array to the hidden fields (so we can make URLs, and post stuff back to the server)
			function writeCharacters() {
				var i = 0;
				var new_position_grid = "";
				var new_costume_grid = "";
				var new_level_grid = "";
				var new_flair_grid = "";
				// This is stupid simple -- since the grids are character_index-indexed, just iterate through the character list and concat the relevant info to better
				for ( i = 0; i < characters.length; i++) {
					new_position_grid = new_position_grid + characters[i].grid_tag;
					// Make sure that numeric fields are padded correctly
					new_costume_grid = new_costume_grid + pad(characters[i].costume.toString(), 2);
					var level = characters[i].level + (60 * characters[i].prestige);
					new_level_grid = new_level_grid + pad(level.toString(), 3);
					new_flair_grid = new_flair_grid + pad(characters[i].flair.toString(), 2);
				}
				for ( i = 0; i < characters.length; i++) {
					new_flair_grid = new_flair_grid + pad(characters[i].source.toString(), 2);
				}
				// Set the hidden fields
				$("#position_grid").val(new_position_grid);
				$("#costume_grid").val(new_costume_grid);
				$("#level_grid").val(new_level_grid);
				$("#flair_grid").val(new_flair_grid);
				// Flag the sig as dirty
				$("#action").val("");
			}

			// Calculate the size of the grid (for dynamic growing of the grid)
			function calculateGridSize() {
				var position_grid = $("#position_grid").val();
				// 14 X 2 is the min size
				var new_grid_cols = 14;
				var new_grid_rows = 2;
				// Iterate over all of the characters
				for ( i = 0; i < characters.length; i++) {
					// Get the grid tag
					var grid_tag = getGridValue(position_grid, i, 6);
					if (grid_tag == "X-1Y-1") {
						continue;
					}
					// Parse out the cols; make the new col size the max index + 2 (so, 1-based, plus a blank row)
					if (parseInt(grid_tag.substr(1, 2), 10) + 2 > new_grid_cols) {
						new_grid_cols = parseInt(grid_tag.substr(1, 2), 10) + 2;
					}

					// Do the same thing for tows
					var row = parseInt(grid_tag.substr(4, 2), 10);
					if (row + 2 > new_grid_rows) {
						new_grid_rows = row + 2;
					}
				}
				// Set the new size into the hidden fields.
				console.log("New row count: " + new_grid_rows.toString());
				$("#hidden_rows").val(new_grid_rows.toString());
				$("#hidden_cols").val(new_grid_cols.toString());
			}

			function loadFlair() {
				var flair_names = $("#flair_names").val().split(";");
				var flair_files = $("#flair_files").val().split(";");
				var flair_indices = $("#flair_indices").val().split(";");
				var flair_positions = $("#flair_positions").val().split(";");
				for ( i = 0; i < flair_names.length; i++) {
					if (flair_names[i].length > 0) {
						var f = new MarvelFlair(parseInt(flair_indices[i]), flair_names[i], flair_files[i], parseInt(flair_positions[i]));
						flair.push(f);
					}
				}
			}

			// Load the characters from the hidden fields into the characters array
			function loadCharacters() {
				// Pull the grids.
				var position_grid = $("#position_grid").val();
				var costume_grid = $("#costume_grid").val();
				var flair_grid = $("#flair_grid").val();

				var num_chars = position_grid.length / 6;
				var char_names = $("#char_names").val().split(";");
				console.log($("#char_costumes").val());
				var cos_chunks = $("#char_costumes").val().replace("'", "").split("|");
				var cos_index_chunks = $("#char_costume_indices").val().split("|");

				var i = 0;
				var costume = 0;
				var x = "";
				var y = "";
				var char_index = "";
				var level_grid = $("#level_grid").val();
				var home_chunks = $("#pen_coords").val().split(";");
				console.log($("#pen_coords").val());
				// For each character...
				for ( i = 0; i < num_chars; i++) {
					char_index = pad(i.toString(), 2);
					// Derive the various control names; we save these so we can monkey about with the controls later.
					var button_name = "#" + char_index + "_button";
					var menu_name = "#" + char_index + "_costumes";
					var level_name = "#" + char_index + "_level";
					var levellabel_name = "#" + char_index + "_levellabel";
					var white_name = "#" + char_index + "_white";
					var green_name = "#" + char_index + "_green";
					var blue_name = "#" + char_index + "_blue";
					var purple_name = "#" + char_index + "_purple";
					var orange_name = "#" + char_index + "_orange";
					var red_name = "#" + char_index + "_red";
					var flair_name = "#" + char_index + "_flair";
					var source_name = "#" + char_index + "_source";

					var flairimage_name = "#" + char_index + "_flairimage";
					var sourceimage_name = "#" + char_index + "_sourceimage";

					// Create a grid tag.
					var grid_tag = getGridValue(position_grid, i, 6);
					if (grid_tag == null) {
						grid_tag = "X-1Y-1";
					}
					// Extract the "home coordinates" -- where the character lives in the character pen so we can put it back there when the user drags it out of the grid.
					var home_chunk = home_chunks[i].split(",");
					// Create a new hero.
					var m = new MarvelHero(char_index, parseFloat(home_chunk[0]), parseFloat(home_chunk[1]), grid_tag, button_name, menu_name, level_name, white_name, green_name, blue_name, purple_name, orange_name, red_name, flair_name, levellabel_name, flairimage_name, source_name, sourceimage_name);
					var level_tag = getGridValue(level_grid, i, 3);
					// Calculate the level and prestige level
					if (level_tag != null) {
						var new_level = parseInt(level_tag, 10);
						if (new_level > 60 && new_level <= 120) {
							m.prestige = 1;
						} else if (new_level > 120 && new_level <= 180) {
							m.prestige = 2;
						} else if (new_level > 180 && new_level <= 240) {
							m.prestige = 3;
						} else if (new_level > 240 && new_level <= 300) {
							m.prestige = 4;
						} else if (new_level > 300 && new_level <= 360) {
							m.prestige = 5;
						}
						m.level = new_level - (60 * m.prestige);
					}
					// Extract the costume
					var costume_tag = getGridValue(costume_grid, i, 2);
					if (costume_tag != null) {
						m.costume = parseInt(costume_tag, 10);
					}
					// Extract the flair
					var flair_tag = getGridValue(flair_grid, i, 2);
					if (flair_tag != null) {
						m.flair = parseInt(flair_tag, 10);
					}

					// Extract the source
					var source_tag = getGridValue(flair_grid, num_chars + i, 2);
					if (source_tag != null) {
						m.source = parseInt(source_tag, 10);
					}

					m.char_name = char_names[i];
					console.log("Loading: " + m.char_name + " with flair " + m.flair.toString());
					// Get the costume names and indices, which are delimited string (and live as arrays in the character object)
					m.costume_names = cos_chunks[i].split("~");
					m.costume_indices = cos_index_chunks[i].split("~");
					characters.push(m);
				}
			}

			// Remove everything from the grid.  This is, thankfully, just a matter of resetting the position grid to all "X-1Y-1".
			function clearLayout() {
				var i = 0;
				for ( i = 0; i < characters.length; i++) {
					var hero = characters[i];
					$(hero.myButton).css({
						top : hero.home_y,
						left : hero.home_x
					});
					hero.grid_tag = "X-1Y-1";
					$(hero.myLevelLabel).hide();
					$(hero.myFlairImage).hide();
				}
				writeCharacters();
				buildGrid();
			}

			// This puts every hero in the grid in alphabetical order, similar to how V1 worked.
			function defaultLayout() {
				var i = 0;
				var grid_cols = $("#hidden_cols").val();

				var cols = parseInt(grid_cols, 10);

				var y = 0;
				var x = 0;

				// This is tricky.  Because of some weirdness, I pass down a list of character indices, ordered by display order, which serves as a map between display order and char index.
				// What you see here is iterating over display order, and pulling the corresponding char index, and then placing the character.
				for ( i = 0; i < characters.length; i++) {
					var char_index = getGridValue($("#display_order").val(), i, 2);
					var hero = characters[parseInt(char_index, 10)];
					var grid_tag = "X" + pad(x.toString(), 2) + "Y" + pad(y.toString(), 2);
					hero.grid_tag = grid_tag;
					x += 1;
					if (x >= cols) {
						x = 0;
						y += 1;
					}
				}
				writeCharacters();
				buildGrid();
			}

			// Pad a string with 0s
			function pad(str, max) {
				return str.length < max ? pad("0" + str, max) : str;
			}

			// Close all character menus
			function closeAllMenus() {
				console.log("Closing all menus!\n");
				for (var i = 0; i < characters.length; i++) {
					$(characters[i].myMenu).hide();
				}
				// Write the grid
				writeCharacters();
				// Render
				buildGrid();
			}

			// Show a character's menu
			function showCharacterMenu(char_index) {
				var hero = null;
				for (var i = 0; i < characters.length; i++) {
					if (i == parseInt(char_index, 10)) {
						hero = characters[i]
					} else {
						$(characters[i].myMenu).hide();
					}
				}
				// If the hero is not placed, do nothing.
				if (hero.grid_tag == "X-1Y-1") {
					return;
				}
				// Put the menu on top of the button
				$(hero.myMenu).css({
					top : $(hero.myButton).position().top - 28.75,
					left : $(hero.myButton).position().left - 8.75
				});
				// Set the level correctly.
				$(hero.myLevel).val(hero.level.toString());
				// Set the flair index correctly
				console.log("Setting flair to: " + hero.flair.toString());
				$(hero.myFlair).val(hero.flair);
				// Set the flair index correctly
				console.log("Setting source to: " + hero.source.toString());
				$(hero.mySource).val(hero.source);
				// Show the menu
				$(hero.myMenu).show('fast');
				// Focus on the level text.
				$(hero.myLevel).focus();
			}

			// Select a hero's costume.
			function selectCostume(char_index, cos_index) {
				// Find the hero
				var hero = characters[parseInt(char_index, 10)];
				// Set the costume index
				hero.costume = parseInt(cos_index, 10);
				// Write the grid
				writeCharacters();
				// Render
				buildGrid();
				// Hide the menu
				$(hero.myMenu).hide('fast');

				// You'll see the above a lot; I based the render code on the hidden fields, so most of these functions just save and re-render, which cuts out a lot of work.
			}

			function flairChange(char_index) {
				console.log("Flair changed!");
				var hero = characters[parseInt(char_index, 10)];
				var flair_index = parseInt($(hero.myFlair).val(), 10);
				hero.flair = flair_index;
				if (hero.flair > 0) {
					for (var i = 0; i < flair.length; i++) {
						if (flair[i].flair_index == flair_index) {
							$(hero.myFlairImage).attr("src", "glyphicons_free/glyphicons/png/" + flair[i].flair_file);
							$(hero.myFlairImage).show();
							break;
						}
					}
				} else {
					$(hero.myFlairImage).hide();
				}
			}

			function sourceChange(char_index) {
				console.log("Flair changed!");
				var hero = characters[parseInt(char_index, 10)];
				var source_index = parseInt($(hero.mySource).val(), 10);
				hero.source = source_index;
				if (hero.source > 0) {
					for (var i = 0; i < flair.length; i++) {
						if (flair[i].flair_index == source_index) {
							$(hero.mySourceImage).attr("src", "glyphicons_free/glyphicons/png/" + flair[i].flair_file);
							$(hero.mySourceImage).show();
							break;
						}
					}
				} else {
					$(hero.mySourceImage).hide();
				}
			}

			// This was a bit of a production, since it has to change the button state.  Seriously, that's what all this code is for.'
			function prestigeChange(char_index, p_level) {
				var hero = characters[parseInt(char_index, 10)];
				// Get the prestige level
				hero.prestige = p_level;

				// For each prestige level, set the hero's prestige buttons correctly'
				if (p_level == 0) {
					$(hero.myWhite).attr("src", "images_new/ui_white_on.png");
					$(hero.myLevelLabel).addClass("prestige_level_0");
				} else {
					$(hero.myWhite).attr("src", "images_new/ui_white_off.png");
					$(hero.myLevelLabel).removeClass("prestige_level_0");
				}
				if (p_level == 1) {
					$(hero.myGreen).attr("src", "images_new/ui_green_on.png");
					$(hero.myLevelLabel).addClass("prestige_level_1");
				} else {
					$(hero.myGreen).attr("src", "images_new/ui_green_off.png");
					$(hero.myLevelLabel).removeClass("prestige_level_1");
				}
				if (p_level == 2) {
					$(hero.myBlue).attr("src", "images_new/ui_blue_on.png");
					$(hero.myLevelLabel).addClass("prestige_level_2");
				} else {
					$(hero.myBlue).attr("src", "images_new/ui_blue_off.png");
					$(hero.myLevelLabel).removeClass("prestige_level_2");
				}
				if (p_level == 3) {
					$(hero.myPurple).attr("src", "images_new/ui_purple_on.png");
					$(hero.myLevelLabel).addClass("prestige_level_3");
				} else {
					$(hero.myPurple).attr("src", "images_new/ui_purple_off.png");
					$(hero.myLevelLabel).removeClass("prestige_level_3");
				}
				if (p_level == 4) {
					$(hero.myOrange).attr("src", "images_new/ui_orange_on.png");
					$(hero.myLevelLabel).addClass("prestige_level_4");
				} else {
					$(hero.myOrange).attr("src", "images_new/ui_orange_off.png");
					$(hero.myLevelLabel).removeClass("prestige_level_4");
				}
				if (p_level == 5) {
					$(hero.myRed).attr("src", "images_new/ui_red_on.png");
					$(hero.myLevelLabel).addClass("prestige_level_5");
				} else {
					$(hero.myRed).attr("src", "images_new/ui_red_off.png");
					$(hero.myLevelLabel).removeClass("prestige_level_5");
				}
			}

			// Change the hero's level
			function levelChange(char_index) {
				console.log("In level change!");
				var hero = characters[parseInt(char_index, 10)];
				var new_level = 0;
				// Check for syntax errors and boundary conditions
				if ($(hero.myLevel).val().length > 0) {
					new_level = parseInt($(hero.myLevel).val(), 10);
					if (isNaN(new_level)) {
						alert("Check the level value entered -- it is not an integer.");
						return;
					} else {
						if (new_level > 60) {
							alert("There is no level higher than 60; please try again.");
							return;
						}
						if (new_level < 0) {
							alert("There is no level lower than 0; please try again.");
							return;
						}
					}
				}
				// Set the level
				hero.level = new_level;
				if (hero.level > 0) {
					// Redraw the level tag.
					$(hero.myLevelLabel).html(new_level.toString());
					$(hero.myLevelLabel).show();
				} else {
					$(hero.myLevelLabel).hide();
					prestigeChange(char_index, 0);
				}
			}

			// Show that blue thingy around the character images.
			function showSelector(char_index) {
				var hero = characters[parseInt(char_index, 10)];
				var element = $('#c_selector').detach();
				$(hero.myButton).append(element);
				$("#c_selector").show();
			}

			// Show that blue thingy around the costumes
			function showCostumeSelector(char_index, cos_index) {
				var button_name = "#" + pad(char_index.toString(), 2) + "_" + pad(cos_index.toString(), 2) + "_button";
				console.log(button_name);
				var element = $('#c_selector').detach();
				$(button_name).append(element);
				$("#c_selector").show();
			}

			// Hide the blue thingy.
			function hideSelector() {
				$("#c_selector").hide();
			}

			// Because everything is growing or shrinking or bopping about, we have to dynamically move elements.  You can see how this does it -- it just offsets the different y coordinates by the height of the above elements.
			function positionElements() {
				var rows = parseInt($("#hidden_rows").val(), 10);
				var cols = parseInt($("#hidden_cols").val(), 10);
				var outer_width = 8.75 + (53.75 * cols);
				$("outerborder").css({
					width : outer_width
				});
				var x = (outer_width - $("#costumepen").width()) / 2;
				$("#costumepen").css({
					left : x
				});
				var y = $("#costumepen").position().top + $("#costumepen").height() + 30;
				x = (outer_width - $("#sig_grid").width()) / 2;
				$("#sig_grid").css({
					top : y,
					left : x
				});
				y += $("#sig_grid").height() + 30;
				x = (outer_width - $("#controls").width()) / 2;
				$("#controls").css({
					top : y,
					left : x
				});
				y += $("#controls").height() + 30;
				x = (outer_width - $("#preview").width()) / 2;
				$("#preview").css({
					top : y,
					left : x
				});
				y += (45 * rows);
				x = (outer_width - $("#markdown_div").width()) / 2;
				$("#markdown_div").css({
					top : y,
					left : x
				});
				y += $("#markdown_div").height() + 30;
				x = (outer_width - $("#footer").width()) / 2;
				$("#footer").css({
					top : y,
					left : x
				});
				y += $("#footer").height() + 100;
				$("#outerborder").height(y);
			}

			// Do some initial stuff.
			function init() {
				loadFlair();
				loadCharacters();
				buildGrid();
				previewSig();
				$("body").click(function(evt) {
					closeAllMenus();
				});
			}

			// The same convenience functions for getting and setting tokens from a fix-length token string.
			function getGridValue(inGrid, inIndex, inGridCellSize) {
				if (inGrid.length <= (inIndex * inGridCellSize)) {
					return null;
				}
				var offset = inIndex * inGridCellSize;
				return inGrid.substr(offset, inGridCellSize);
			}

			function setGridValue(inGrid, inValue, inIndex, inGridCellSize) {
				if (inGrid.length <= (inIndex * inGridCellSize)) {
					return null;
				}
				var offset = inIndex * inGridCellSize;
				var new_val = pad(inValue, inGridCellSize);
				return inGrid.substr(0, offset) + new_val + inGrid.substr(offset + inGridCellSize);
			}

			// THis function handles what happens when a character is dragged and then dropped on the interface
			function dropCharacter(char_index, ui) {
				var hero = characters[parseInt(char_index, 10)];
				var drop_x = ui.position.left + 22.5;
				var drop_y = ui.position.top + 22.5;
				var grid_cols = $("#hidden_cols").val();
				var grid_rows = $("#hidden_rows").val();

				var rows = parseInt(grid_rows, 10);
				var cols = parseInt(grid_cols, 10);

				var y = 0;
				var x = 0;
				var i = 0;
				var grid_tag = "";
				var index = 0;
				var hit = false;
				var level_label_name = "#" + pad(char_index.toString(), 2) + "_levellabel";
				// Iterate over the grid and see if the drop coordinates are inside of a given grid position
				for ( y = 0; y < rows; y++) {
					for ( x = 0; x < cols; x++) {
						grid_tag = "X" + pad(x.toString(), 2) + "Y" + pad(y.toString(), 2);
						var id = "#" + grid_tag + "_gridblock";
						if (drop_x >= $(id).position().left && drop_x <= $(id).position().left + 45 && drop_y >= $(id).position().top && drop_y <= $(id).position().top + 45) {
							hit = true;
							break;
						}
					}
					if (hit)
						break;
				}
				// If there was a hit...
				if (hit) {
					// See if something is already in the grid
					var clasher = null;
					for ( i = 0; i < characters.length; i++) {
						if (characters[i].grid_tag == grid_tag) {
							clasher = characters[i];
							break;
						}
					}
					// If there was something in the grid, swap it to wherever the thing we dropped was.
					if (clasher != null) {
						clasher.grid_tag = hero.grid_tag;
						console.log("Clasher grid tag: " + clasher.grid_tag);
						console.log("Clasher home: " + clasher.home_x.toString() + ", " + clasher.home_y.toString());
						if (clasher.grid_tag == "X-1Y-1") {
							$(clasher.myButton).css({
								top : clasher.home_y,
								left : clasher.home_x
							});
						}
					}
					// Set the hero's grid tag'
					hero.grid_tag = grid_tag;
					// Show the hero's level, now that it is on the grid.'
					if (hero.level > 0) {
						$(hero.myLevelLabel).show();
					} else {
						$(hero.myLevelLabel).hide();
					}
					if (hero.flair >= 0) {
						$(hero.myFlairImage).show();
					}
					if (hero.source >= 0) {
						$(hero.mySourceImage).show();
					}
				} else {
					// If there was no hit, then put the hero back in the character pen.
					$(hero.myButton).css({
						top : hero.home_y,
						left : hero.home_x
					});
					hero.grid_tag = "X-1Y-1";
					$(hero.myLevelLabel).hide();
					if (hero.flair >= 0) {
						$(hero.myFlairImage).hide();
					}
					if (hero.source >= 0) {
						$(hero.mySourceImage).hide();
					}
				}
				writeCharacters();
				buildGrid();
			}

			// This is the render function.
			function buildGrid() {
				calculateGridSize();
				positionElements();
				// Remove the existing "drop zones" from the sig area.
				$("div[id$='_gridblock']").remove();

				var grid_cols = $("#hidden_cols").val();
				var grid_rows = $("#hidden_rows").val();

				var rows = parseInt(grid_rows, 10);
				var cols = parseInt(grid_cols, 10);
				var height = 8.75 + (rows * (45 + 8.75));
				var width = 8.75 + (cols * (45 + 8.75));
				$("#sig_grid").css({
					height : height,
					width : width
				});
				positionElements();

				// Draw the grid squares
				var y = 0;
				var x = 0;
				var index = 0;
				for ( y = 0; y < rows; y++) {
					for ( x = 0; x < cols; x++) {
						var grid_tag = "X" + pad(x.toString(), 2) + "Y" + pad(y.toString(), 2);
						var grid_image = "";
						var xoff = $("#sig_grid").position().left + 8.75 + (x * (45 + 8.75));
						var yoff = $("#sig_grid").position().top + 8.75 + (y * (45 + 8.75));
						var found = false;
						for ( index = 0; index < characters.length; index++) {
							// If there is a character in this grid square...
							if (characters[index].grid_tag == grid_tag) {
								$(characters[index].myButton).css({
									top : yoff + 1,
									left : xoff + 1
								});
								// Render the various elements of the heros button (set the image to the correct costume, draw the level)
								var image_name = "#" + pad(index.toString(), 2) + "_displayimage";
								var costume = pad(characters[index].costume.toString(), 2);
								var image_el = "#" + pad(index.toString(), 2) + "_" + costume + "_costume";
								grid_image = $(image_el).attr("src");
								$(image_name).attr("src", grid_image);
								var level_label_name = "#" + pad(characters[index].char_index.toString(), 2) + "_levellabel";
								$(level_label_name).html(characters[index].level);
								// This sets the button state correctly
								prestigeChange(characters[index].char_index, characters[index].prestige);
								if (characters[index].flair > 0) {
									for (var p = 0; p < flair.length; p++) {
										if (flair[p].flair_index == characters[index].flair) {
											$(characters[index].myFlairImage).attr("src", "glyphicons_free/glyphicons/png/" + flair[p].flair_file);
											$(characters[index].myFlairImage).show();
										}
									}
								} else {
									$(characters[index].myFlairImage).hide();
								}
								if (characters[index].source > 0) {
									for (var p = 0; p < flair.length; p++) {
										if (flair[p].flair_index == characters[index].source) {
											$(characters[index].mySourceImage).attr("src", "glyphicons_free/glyphicons/png/" + flair[p].flair_file);
											$(characters[index].mySourceImage).show();
										}
									}
								} else {
									$(characters[index].mySourceImage).hide();
								}
								// Show the level
								if (characters[index].level > 0) {
									$(level_label_name).show();
								} else {
									$(level_label_name).hide();
								}
								found = true;
								break;
							}
						}
						// Add the block to the grid
						var id = grid_tag + "_gridblock";

						var image_id = x.toString() + "_" + y.toString() + "_image";
						var text = "<div class='GridButton' id='" + id + "' style='left:" + xoff.toString() + "px; top:" + yoff.toString() + "px;'><img id='image_id' class='CharacterImage' src='images_new/blank.jpg'  /></div>";
						$("#outerborder").append(text);
					}
				}
				positionElements();
			}
		</script>
	</head>
	<body>
		<div id='helptext' name='helptext'>
			<table border=0 style='width:100%'>
				<tr>
					<td><a href="help.html" target="_blank">Help</a></td>
					<td><a href="configuresig_old.php" target="_blank">Old version</a></td>
				</tr>
			</table>
		</div>
		<div id='version' name='version'>
			<p>
				2.3.1
			</p>
		</div>
		<form id='marvelform' name='marvelform' method='post' action='configuresig.php'>
			<div id="outerborder">
				<img class='CharacterSelector' src='images_new/UI_Selector.png' id='c_selector'/>

				<?php

				if ($_POST['tagSubmit']) {
					generateSig(false);
				}
				populateCharacterMenu();
				?>
				<div id='sig_grid'>
					<div class='titleclause'>
						Drop characters here
					</div>
				</div>
				<div id='preview'>

				</div>
				<div id='markdown_div'>
					<p>
						Markdown (put this in your signature on the forums):
					</p>
					<textarea id='markdown' name='markdown' rows='4' cols='100'></textarea>
				</div>
				<div id='footer'>
					<p>
						All images are courtesy of <a href='https://forums.marvelheroes.com/discussion/42635/interactive-hero-roster-2-0'>@zztodd</a> on the Marvel Heroes forums.  Thanks, @zztodd!
					</p>
					<p>
						Tooltip ideas and guidance courtesy of @docslax on the forums.
					</p>
					<p>
						Marvel Heroes content and materials are trademarks and copyrights of Gazillion Entertainment and its licensors.
					</p>
					<p>
						Flair icons are courtesy of <a href='http://glyphicons.com/'>GLYPHICONS</a>, and are used under <a href='http://creativecommons.org/licenses/by/3.0/'>Creative Commons Attribution 3.0 Unported (CC BY 3.0)</a> license.
					</p>
					<p>
						This tool is available under an <a href='http://opensource.org/licenses/MIT'>MIT License</a>.  Source code can be found in this <a href='https://github.com/seanwmcginnis/marvelheroessig'>Github repository</a>.
					</p>
				</div>
			</div>
		</form>
		<?php

		// The PHP is mostly concerned with loading and saving; all the heavy lifting is done in the javascript code.

		if (!empty($_POST["action"])) {
			if ($_POST["action"] == "save") {
				saveSig(false);
			}

		}

		// Log a user visit; I was curious, even though the site has metric software.
		function logvisit($mysqli) {
			$ip_address = $_SERVER['REMOTE_ADDR'];
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
			$referrer = $_SERVER['HTTP_REFERER'];
			$request_uri = $_SERVER['REQUEST_URI'];

			$query = "INSERT INTO visitors(ip_address, user_agent, referrer, target_url) VALUES('$ip_address', '$user_agent', '$referrer', '$request_uri')";
			$result = $mysqli -> query($query);
		}

		// Show an alert with javascript.
		function showAlert($message) {
			print "\n\n
		<script language='javascript'>
\n\n alert('$message'); \n\n
		</script>\n\n";
		}

		// Show an alert with javascript.
		function debugPrint($message) {
			return;
			/* print "\n\n
			 <script language='javascript'>
			 \n\n console.log('$message'); \n\n
			 </script>\n\n";*/
		}

		// Delete a cookie
		function clearCookie($key) {
			if (isset($_COOKIE[$key])) {
				unset($_COOKIE[$key]);
				setcookie('key', '', time() - 3600);
				// empty value and old timestamp
			}
		}

		// Oh!  Hey!  It's those grid functions again!
		function setGridValue($inGrid, $inValue, $inIndex, $inGridCellSize) {
			if (strlen($inGrid) <= ($inIndex * $inGridCellSize)) {
				return $inGrid;
			}
			$offset = $inIndex * $inGridCellSize;
			$new_val = str_pad($inValue, $inGridCellSize, "0", STR_PAD_LEFT);
			return substr($inGrid, 0, $offset) . $new_val . substr($inGrid, $offset + $inGridCellSize);
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
				$char_index = $characters[$i] -> get_char_index();
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

		// Load a bunch of information into hidden fields, which will be parsed from the client.
		function populateHiddens($characters, $flair) {
			$num_characters = count($characters);
			// Convert legacy cookies
			if (empty($_POST['position_grid']) && empty($_COOKIE["marvelsig_position_grid"]) && !empty($_COOKIE["marvelsig_config"])) {
				convertLegacyCookies($characters);
			}
			print "
		<div id='hiddens'>
			";
			$default_cols = 14;
			$default_rows = ceil($num_characters / $default_cols);
			$val = $default_cols;
			// These are actually no longer used; I could hard-code them into the site.
			if (!empty($_POST['hidden_cols'])) {
				$val = $_POST['hidden_cols'];
			}
			print "
			<input type='hidden' id='hidden_cols' name='hidden_cols' value='$val'>
			";
			$val = $default_rows;
			if (!empty($_POST['hidden_rows'])) {
				$val = $_POST['hidden_rows'];
			}
			print "
			<input type='hidden' id='hidden_rows' name='hidden_rows' value='$val'>
			";

			// Create a default position grid, in case we don't have one already.
			$default_position_grid = "X-1Y-1";
			for ($i = 1; $i < $num_characters; $i++) {
				$default_position_grid = $default_position_grid . "X-1Y-1";
			}
			// Pull the relevant bits from the form or the cookies, as appropriate.
			$val = $default_position_grid;
			if (!empty($_POST['position_grid'])) {
				$val = $_POST['position_grid'];
			} else if (!empty($_COOKIE["marvelsig_position_grid"])) {
				$val = $_COOKIE['marvelsig_position_grid'];
			}
			while (strlen($val) < (6 * $num_characters)) {
				$val = $val . "X-1Y-1";
			}
			print "
			<input type='hidden' id='position_grid' name='position_grid' value='$val'>
			";

			// Repeat with the level grid
			$default_level_grid = "000";
			for ($i = 1; $i < $num_characters; $i++) {
				$default_level_grid = $default_level_grid . "000";
			}
			$val = $default_level_grid;
			if (!empty($_POST['level_grid'])) {
				$val = $_POST['level_grid'];
			} else if (!empty($_COOKIE["marvelsig_level_grid"])) {
				$val = $_COOKIE['marvelsig_level_grid'];
			}
			while (strlen($val) < (3 * $num_characters)) {
				$val = $val . "000";
			}
			print "
			<input type='hidden' id='level_grid' name='level_grid' value='$val'>
			";

			// AND the display order map
			$display_order = "";
			for ($i = 0; $i < $num_characters; $i++) {
				$display_order = $display_order . str_pad($characters[$i] -> get_char_index(), 2, "0", STR_PAD_LEFT);
			}
			$val = $display_order;
			print "
			<input type='hidden' id='display_order' name='display_order' value='$val'>
			";

			// AND the costume grid
			$default_costume_grid = "00";
			for ($i = 1; $i < $num_characters; $i++) {
				$default_costume_grid = $default_costume_grid . "00";
			}
			$val = $default_costume_grid;
			if (!empty($_POST['costume_grid'])) {
				$val = $_POST['costume_grid'];
			} else if (!empty($_COOKIE["marvelsig_costume_grid"])) {
				$val = $_COOKIE['marvelsig_costume_grid'];
			}
			while (strlen($val) < (2 * $num_characters)) {
				$val = $val . "00";
			}
			print "
			<input type='hidden' id='costume_grid' name='costume_grid' value='$val'>
			";

			// AND the flair grid
			$default_flair_grid = "00";
			for ($i = 1; $i < 2 * $num_characters; $i++) {
				$default_flair_grid = $default_flair_grid . "00";
			}
			$val = $default_flair_grid;
			if (!empty($_POST['flair_grid'])) {
				$val = $_POST['flair_grid'];
			} else if (!empty($_COOKIE["marvelsig_flair_grid"])) {
				$val = $_COOKIE['marvelsig_flair_grid'];
			}
			while (strlen($val) < (4 * $num_characters)) {
				$val = $val . "00";
			}
			print "
			<input type='hidden' id='flair_grid' name='flair_grid' value='$val'>
			";

			// This bit of business writes the home coordinates of the characters to the pen_coords arra.
			$val = "";
			$j = 0;
			for ($i = 0; $i < $num_characters; $i++) {
				for ($j = 0; $j < $num_characters; $j++) {
					if ($characters[$j] -> get_char_index() == $i) {
						$val = $val . strval($characters[$j] -> get_home_x()) . "," . strval($characters[$j] -> get_home_y()) . ";";
						break;
					}
				}
			}
			print "
			<input type='hidden' id='pen_coords' name='pen_coords' value='$val'>
			";

			// Write the costume names and costume indices to fields; this is for generating the tooltips.
			$char_names = "";
			$char_costumes = "";
			$char_costume_indices = "";
			$cos_count = 0;
			for ($i = 0; $i < $num_characters; $i++) {
				for ($j = 0; $j < $num_characters; $j++) {
					if ($characters[$j] -> get_char_index() == $i) {
						$char_names = $char_names . htmlspecialchars($characters[$j] -> get_char_name(), ENT_QUOTES) . ";";
						$cos_count = $characters[$j] -> get_cos_names_count();
						for ($k = 0; $k < $cos_count; $k++) {
							$char_costumes = $char_costumes . htmlspecialchars($characters[$j] -> pop_cos_names($k), ENT_QUOTES) . "~";
							$char_costume_indices = $char_costume_indices . strval($characters[$j] -> pop_cos_indices($k)) . "~";
						}
						$char_costumes = $char_costumes . "|";
						$char_costume_indices = $char_costume_indices . "|";
						break;
					}
				}
			}
			print "
			<input type='hidden' id='char_names' name='char_names' value='$char_names'>
			";
			print "
			<input type='hidden' id='char_costumes' name='char_costumes' value='$char_costumes'>
			";
			print "
			<input type='hidden' id='char_costume_indices' name='char_costume_indices' value='$char_costume_indices'>
			";

			$flair_names = "";
			$flair_indices = "";
			$flair_files = "";
			$flair_positions = "";

			for ($i = 1; $i < count($flair); $i++) {
				$flair_names = $flair_names . $flair[$i] -> get_flair_name() . ";";
				$flair_indices = $flair_indices . $flair[$i] -> get_flair_index() . ";";
				$flair_files = $flair_files . $flair[$i] -> get_flair_file() . ";";
				$flair_positions = $flair_positions . $flair[$i] -> get_flair_position() . ";";
			}
			print "
			<input type='hidden' id='flair_names' name='flair_names' value='$flair_names'>
			";
			print "
			<input type='hidden' id='flair_files' name='flair_files' value='$flair_files'>
			";
			print "
			<input type='hidden' id='flair_positions' name='flair_positions' value='$flair_positions'>
			";
			print "
			<input type='hidden' id='flair_indices' name='flair_indices' value='$flair_indices'>
			";

			$val = $_POST['action'];
			print "
			<input type='hidden' id='action' name='action' value='$val'>
			";
			print "
		</div>";
		}

		// This code attaches the various events to the different controls on the client side.
		function buildReadyScript($characters) {
			$ready_script = "
		<script>
			$(document).ready(function(){init();\n";
			for ($i = 0; $i < count($characters); $i++) {
				// Attach the hover commands to the character button
				$button_name = str_pad(strval($characters[$i] -> get_char_index()), 2, "0", STR_PAD_LEFT) . "_button";
				$char_index = $characters[$i] -> get_char_index();
				$ready_script = $ready_script . "$( '#$button_name' ).hover(function() {showSelector($char_index);}, function() {hideSelector();});\n";
				// Make it draggable
				$ready_script = $ready_script . "$( '#$button_name' ).draggable({stop: function( event, ui ) {dropCharacter($char_index, ui);}});\n";
				// Make the costume menu pop up
				$ready_script = $ready_script . "$( '#$button_name' ).click(function(evt) {showCharacterMenu($char_index);evt.stopPropagation();});\n";
				// Associate the hover events with the costume buttons
				for ($j = 0; $j < $characters[$i] -> get_cos_indices_count(); $j++) {
					$cos_index = $characters[$i] -> pop_cos_indices($j);
					$button_name = str_pad(strval($characters[$i] -> get_char_index()), 2, "0", STR_PAD_LEFT) . "_" . str_pad($cos_index, 2, "0", STR_PAD_LEFT) . "_button";
					$ready_script = $ready_script . "$( '#$button_name' ).hover(function() {showCostumeSelector($char_index,$cos_index);}, function() {hideSelector();});\n";
					$ready_script = $ready_script . "$( '#$button_name' ).click(function(evt) {selectCostume($char_index, $cos_index);evt.stopPropagation();});\n";
				}
				// Associate click events with the prestige buttons
				$level_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_level";
				$white_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_white";
				$green_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_green";
				$blue_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_blue";
				$purple_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_purple";
				$orange_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_orange";
				$red_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_red";
				$flair_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_flair";
				$source_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_source";

				$ready_script = $ready_script . "$( '#$flair_name' ).click(function(evt) {evt.stopPropagation();});\n";
				$ready_script = $ready_script . "$( '#$source_name' ).click(function(evt) {evt.stopPropagation();});\n";
				$ready_script = $ready_script . "$( '#$level_name' ).click(function(evt) {evt.stopPropagation();});\n";
				$ready_script = $ready_script . "$( '#$flair_name' ).change(function() {flairChange($char_index);});\n";
				$ready_script = $ready_script . "$( '#$source_name' ).change(function() {sourceChange($char_index);});\n";
				$ready_script = $ready_script . "$( '#$level_name' ).change(function() {levelChange($char_index);});\n";
				$ready_script = $ready_script . "$( '#$white_name' ).click(function(evt) {prestigeChange($char_index, 0);evt.stopPropagation();});\n";
				$ready_script = $ready_script . "$( '#$green_name' ).click(function(evt) {prestigeChange($char_index, 1);evt.stopPropagation();});\n";
				$ready_script = $ready_script . "$( '#$blue_name' ).click(function(evt) {prestigeChange($char_index, 2);evt.stopPropagation();});\n";
				$ready_script = $ready_script . "$( '#$purple_name' ).click(function(evt) {prestigeChange($char_index, 3);evt.stopPropagation();});\n";
				$ready_script = $ready_script . "$( '#$orange_name' ).click(function(evt) {prestigeChange($char_index, 4);evt.stopPropagation();});\n";
				$ready_script = $ready_script . "$( '#$red_name' ).click(function(evt) {prestigeChange($char_index, 5);evt.stopPropagation();});\n";
			}
			$ready_script = $ready_script . "});
		</script>";
			return $ready_script;
		}

		// This builds all of the wacky divs that hold the costume menus.
		function buildCostumeMenus($characters, $flair) {
			$costume_menus = "";
			$xoff_costume = 8.75;
			$yoff_costume = 28.75;
			// Iterate over the characters
			for ($i = 0; $i < count($characters); $i++) {
				$curr_costume = "";
				$xoff_costume = 8.75;
				$yoff_costume = 28.75;
				$char_index = $characters[$i] -> get_char_index();
				$menu_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_costumes";
				// This builds the costume buttons and puts them inside the character div.
				for ($j = 0; $j < $characters[$i] -> get_cos_indices_count(); $j++) {
					$cos_index = $characters[$i] -> pop_cos_indices($j);
					$button_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_" . str_pad($cos_index, 2, "0", STR_PAD_LEFT) . "_button";
					$image_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_" . str_pad($cos_index, 2, "0", STR_PAD_LEFT) . "_costume";
					$imagefile = $characters[$i] -> pop_cos_images($j);
					$imagetitle = htmlspecialchars($characters[$i] -> pop_cos_names($j), ENT_QUOTES);
					$curr_costume = $curr_costume . "
		<div class='CostumeButton' title='$imagetitle' id='$button_name' style='left:" . $xoff_costume . "px; top:" . $yoff_costume . "px'><img id='$image_name' class='CostumeImage' src='$imagefile'/>
		</div>";
					$xoff_costume += (45 + 8.75);
					if ($xoff_costume > 270) {
						$yoff_costume += (45 + 8.75);
						$xoff_costume = 8.75;
					}
				}
				if ($xoff_costume > 8.75) {
					$yoff_costume += (45 + 8.75);
				}
				// This creates the level control and the prestige buttons and puts them above the buttons (this is second because I added this feature after my initial attempt)
				$level_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_level";
				$white_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_white";
				$green_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_green";
				$blue_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_blue";
				$purple_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_purple";
				$orange_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_orange";
				$red_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_red";
				$flair_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_flair";
				$source_name = str_pad($char_index, 2, "0", STR_PAD_LEFT) . "_source";
				$costume_menus = $costume_menus . "
		<div class='CostumeMenu' id='$menu_name' style='width:330px; height:" . strval($yoff_costume) . "px'>
			";
				$costume_menus = $costume_menus . "
			<input type='text' id='$level_name' name='$level_name' class='LevelArea'>
			";
				$costume_menus = $costume_menus . "<img src='images_new/ui_white_off.png' title='Prestige Level 0' id='$white_name' name='$white_name' class='whiteButton'/>";
				$costume_menus = $costume_menus . "<img src='images_new/ui_green_off.png' title='Prestige Level 1' id='$green_name' name='$green_name' class='greenButton'/>";
				$costume_menus = $costume_menus . "<img src='images_new/ui_blue_off.png' title='Prestige Level 2' id='$blue_name' name='$blue_name' class='blueButton'/>";
				$costume_menus = $costume_menus . "<img src='images_new/ui_purple_off.png' title='Prestige Level 3' id='$purple_name' name='$purple_name' class='purpleButton'/>";
				$costume_menus = $costume_menus . "<img src='images_new/ui_orange_off.png' title='Prestige Level 4' id='$orange_name' name='$orange_name' class='orangeButton'/>";
				$costume_menus = $costume_menus . "<img src='images_new/ui_red_off.png' title='Prestige Level 5' id='$red_name' name='$red_name' class='redButton'/>";
				$costume_menus = $costume_menus . "
			<select name='$flair_name' class='FlairArea' id='$flair_name'>
				";
				for ($k = 0; $k < count($flair); $k++) {
					if ($flair[$k] -> get_flair_position() == 0) {
						$flair_name = $flair[$k] -> get_flair_name();
						$flair_index = $flair[$k] -> get_flair_index();
						$costume_menus = $costume_menus . "<option value='$flair_index'>$flair_name</option>";
					}
				}
				$costume_menus = $costume_menus . "
			</select>";

				$costume_menus = $costume_menus . "
			<select name='$source_name' class='SourceArea' id='$source_name'>
				";
				for ($k = 0; $k < count($flair); $k++) {
					if ($flair[$k] -> get_flair_index() == 0 || $flair[$k] -> get_flair_position() == 1) {
						$flair_name = $flair[$k] -> get_flair_name();
						$flair_index = $flair[$k] -> get_flair_index();
						$costume_menus = $costume_menus . "<option value='$flair_index'>$flair_name</option>";
					}
				}
				$costume_menus = $costume_menus . "
			</select>";
				$costume_menus = $costume_menus . $curr_costume . "
		</div>";
			}
			return $costume_menus;
		}

		// This builds the "costume pen" (where characters get dragged from)
		function buildCostumePen(&$characters, &$yoff) {
			$main_menu = "";
			$xoff = 81 + 8.75;

			$button_name = "";
			$menu_name = "";

			// For each character...
			for ($i = 0; $i < count($characters); $i++) {
				// Create the button (the div) and the image (which we need to change when the costume changes), as well as the level label
				$button_name = str_pad(strval($characters[$i] -> get_char_index()), 2, "0", STR_PAD_LEFT) . "_button";
				$image_name = str_pad(strval($characters[$i] -> get_char_index()), 2, "0", STR_PAD_LEFT) . "_displayimage";
				$ll_name = str_pad(strval($characters[$i] -> get_char_index()), 2, "0", STR_PAD_LEFT) . "_levellabel";
				$fi_name = str_pad(strval($characters[$i] -> get_char_index()), 2, "0", STR_PAD_LEFT) . "_flairimage";
				$si_name = str_pad(strval($characters[$i] -> get_char_index()), 2, "0", STR_PAD_LEFT) . "_sourceimage";
				$imagefile = "";
				if ($characters[$i] -> get_cos_images_count() > 1) {
					$imagefile = $characters[$i] -> pop_cos_images(1);
				} else {
					$imagefile = $characters[$i] -> pop_cos_images(0);
				}
				$button_title = $characters[$i] -> get_char_name();
				// Put the HTML together
				$main_menu = $main_menu . "
		<div class='CharacterButton ui-widget-content' title='$button_title' id='$button_name' style='left:" . $xoff . "px; top:" . $yoff . "px'><img id='$image_name' class='CharacterImage' src='$imagefile'  /><div id='$ll_name' style='left:25px; top:30px' name='$ll_name' class='prestige_level_0'></div><img id='$fi_name' style='left:34px; top:3px; display:none;' name='$fi_name' class='flair_image' src=''></img>
			<img id='$si_name' style='left:0px; top:22px; display:none;' name='$si_name' class='source_image' src=''></img>
		</div>\n";
				// Set the home position
				$characters[$i] -> set_home_x($xoff);
				$characters[$i] -> set_home_y($yoff);
				// print strval($xoff) . "," . strval($yoff) . "\n";
				$xoff = $xoff + (45 + 8.75);
				if ($xoff >= 681) {
					$xoff = 81 + 8.75;
					$yoff += (45 + 8.75);
				}
			}
			if ($xoff > 89.75) {
				$yoff += (45 + 8.75);
			}
			return $main_menu;
		}

		// This pulls it all together and renders the bulk of the page.
		function populateCharacterMenu() {
			$yoff = 23.75;
			require_once ('marvelheroes_classes.php');
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, "marvelheroesdb");
			logvisit($mysqli);
			// Load the heroes
			$query = "SELECT * FROM character_images ORDER BY display_position, costume_index";
			$result = $mysqli -> query($query);

			$last_char = "";
			$last_char_index = "";

			$ready_script = "";

			$characters = array();

			// This puts the heroes in an array -- old hat by now.
			while ($myrow = $result -> fetch_assoc()) {
				$char = NULL;
				$char_index = strval($myrow['character_index']);
				$found = false;
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
				if (!$found) {
					$char = new MarvelHero();
					$char -> set_char_index(strval($myrow['character_index']));
					$char -> set_char_name($myrow['character_name']);
					$char -> push_cos_indices(strval($myrow['costume_index']));
					$char -> push_cos_images("images_new/" . $myrow['image_file']);
					$char -> push_cos_names($myrow['costume_name']);
					array_push($characters, $char);
				}
			}
			$result -> close();

			$query = "SELECT * FROM flair ORDER BY flair_index";
			$result = $mysqli -> query($query);

			$flair = array();

			// This puts the heroes in an array -- old hat by now.
			while ($myrow = $result -> fetch_assoc()) {
				$newflair = new MarvelFlair();
				$newflair -> set_flair_index(intval($myrow['flair_index']));
				$newflair -> set_flair_position(intval($myrow['flair_position']));
				$newflair -> set_flair_name($myrow['flair_name']);
				$newflair -> set_flair_file($myrow['flair_file']);
				array_push($flair, $newflair);
			}
			$result -> close();

			// Call the various functions
			print buildCostumePen($characters, $yoff);
			// THIS: This is the blue box around the heroes.  I put everything on the same level in the bounding div to make dragging-and-dropping easier, so this thing is just rendered behind the other elements to look like it contains them.
			print "
		<div id='costumepen' style='height:" . $yoff . "'>
			";
			print "
			<div class='titleclause'>
				Drag characters from here
			</div>
			";
			print "
		</div>";

			print buildReadyScript($characters);
			print buildCostumeMenus($characters, $flair);
			print populateHiddens($characters, $flair);
			buildControls($mysqli);
			$mysqli -> close();
		}

		// Build the control code.
		function buildControls($mysqli) {
			// This is profoundly obvious stuff -- it puts the values in the controls.
			print "
		<div id='controls'>
			<div class='titleclause'>
				Settings
			</div>
			<center>
				<table class='DataTable' cellpadding='8'>
					<tbody>
						";
			$keyword = $_POST['saved_keyword'];
			if (is_null($keyword)) {
				$keyword = $_COOKIE["marvelsig_keyword"];
			}
			if (is_null($keyword)) {
				$keyword = "";
			}
			$view_mode = $_POST['view_mode'];
			if (is_null($view_mode)) {
				$view_mode = $_COOKIE["marvelsig_view_mode"];
			}
			if (is_null($view_mode)) {
				$view_mode = 0;
			}
			$include_link = $_POST['include_link'];
			if (is_null($include_link)) {
				$include_link = $_COOKIE["marvelsig_include_link"];
			}
			if (is_null($include_link)) {
				$include_link = 1;
			}
			$font = intval($_POST['font']);
			if (is_null($font)) {
				$font = intval($_COOKIE["marvelsig_font"]);
			}
			if (is_null($font)) {
				$font = 0;
			}
			$border_type = intval($_POST['border_type']);
			if (is_null($border_type)) {
				$border_type = intval($_COOKIE["marvelsig_border_type"]);
			}
			if (is_null($border_type)) {
				$border_type = 0;
			}
			debugPrint("Border type in create: " . strval($border_type));

			$include_link = intval($_POST['include_link']);
			if (is_null($include_link)) {
				$include_link = intval($_COOKIE["marvelsig_include_link"]);
			}
			if (is_null($include_link)) {
				$include_link = 1;
			}
			$include_tooltips = intval($_POST['include_tooltips']);
			if (is_null($include_tooltips)) {
				$include_tooltips = intval($_COOKIE["marvelsig_include_tooltips"]);
			}
			if (is_null($include_tooltips)) {
				$include_tooltips = 1;
			}

			// The fonts are loaded from the database.
			// FONTS
			print "
						<tr>
							<td>Font</td><td>
							<select name='font' id='font'>
								";
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
			print "
							</select></td>";
			// SIG SHAPE
			print "<td>Shape</td><td>
							<select name='view_mode' id='view_mode'>
								";
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
			print "
							</select></td>
						</tr>";

			// INCLUDE LINK
			print "
						<tr>
							<td>Link</td><td>
							<select name='include_link' id='include_link'>
								";
			print "<option value='1'";
			if ($include_link == 1) {
				print " selected";
			}
			print ">Include a link in markdown</option>";
			print "<option value='0'";
			if ($include_link == 0) {
				print " selected";
			}
			print ">Omit the link</option>";
			print "
							</select></td>";

			// TOOLTIPS
			print "<td>Tooltips in markdown</td><td>
							<select name='include_tooltips' id='include_tooltips'>
								";
			print "<option value='1'";
			if ($include_tooltips == 1) {
				print " selected";
			}
			print ">Create tooltips with level</option>";
			print "<option value='2'";
			if ($include_tooltips == 2) {
				print " selected";
			}
			print ">Create tooltips without level</option>";
			print "<option value='0'";
			if ($include_tooltips == 0) {
				print " selected";
			}
			print ">Do not add tooltips</option>";
			print "
							</select></td>
						</tr>";

			// BORDER TYPE
			print "
						<tr>
							<td>Border Type</td><td>
							<select name='border_type' id='border_type'>
								";
			print "<option value='0'";
			if ($border_type == 0) {
				print " selected";
			}
			print ">Black with Blue Outer Bound</option>";
			print "<option value='1'";
			if ($border_type == 1) {
				print " selected";
			}
			print ">Just Black</option>";
			print "<option value='2'";
			if ($border_type == 2) {
				print " selected";
			}
			print ">None.  No borders.</option>";
			print "<option value='3'";
			 if ($border_type == 3) {
			 print " selected";
			 }
			 print ">Blue Portrait Frame</option>";

			print "
							</select></td>";

			print "<td></td><td></td>
						</tr>";
			// KEYWORD
			print "
						<tr>
							<td>Keyword</td><td>
							<input type='text' name='saved_keyword' id='saved_keyword' value='$keyword'>
							</td>";
			print "<td>Password</td><td>
							<input type='password' name='saved_password' id='saved_password' value=''>
							</td>
						</tr>
						";
			print "
						<tr>
							<td colspan=4 style='text-align:center'>
							<table border=0 style='width:100%'>
								<tr>
									<td><a href='javascript:previewSig()'>Preview Sig</a></td>";
			print "<td style='text-align:center'><a href='javascript:defaultLayout()'>Default Layout</a></td>";
			print "<td style='text-align:center'><a href='javascript:clearLayout()'>Clear Layout</a></td>";
			print "<td style='text-align:center'><a href='javascript:loadSigFromDatabase()'>Load by Keyword</a></td>";
			print "<td style='text-align:center'><a href='javascript:saveSig()'>Save Sig</a></td>";
			print "
								</tr>
							</table></td>";
			print "
					</tbody>
				</table>
				";
			print "
		</div>";
		}

		function loadSigFromDatabase() {
			$keyword = $_POST['saved_keyword'];
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, "marvelheroesdb");
			$query = "SELECT config, chars_per_row, view_mode, font, position_grid, level_grid, costume_grid, border_type, flair_grid, include_link, include_tooltips FROM saved_config WHERE keyword=?";
			$stmt = $mysqli -> prepare($query);
			$stmt -> bind_param("s", $keyword);
			$stmt -> execute();
			$stmt -> bind_result($db_config, $db_chars_per_row, $db_view_mode, $db_font_index, $db_position_grid, $db_level_grid, $db_costume_grid, $db_border_type, $db_flair_grid, $db_include_link, $db_include_tooltips);
			$stmt -> fetch();
			$_POST['position_grid'] = $db_position_grid;
			$_POST['costume_grid'] = $db_costume_grid;
			$_POST['level_grid'] = $db_level_grid;
			$_POST['flair_grid'] = $db_flair_grid;
			$_POST['include_link'] = $db_include_link;
			$_POST['include_tooltips'] = $db_include_tooltips;
			$_POST['font'] = $db_font_index;
			$_POST['view_mode'] = $db_view_mode;
			$_POST['border_type'] = $db_border_type;
			debugPrint("Border type: " . strval($db_border_type));
			$_POST['action'] = "";
			$stmt -> close();
			$mysqli -> close();
		}

		// This actually saves the sig, which is just writing it to the DB (the rendering is handled client-side now)
		function saveSig($fromHeader) {
			$position_grid = $_POST['position_grid'];
			$costume_grid = $_POST['costume_grid'];
			$level_grid = $_POST['level_grid'];
			$flair_grid = $_POST['flair_grid'];
			$include_link = $_POST['include_link'];
			$include_tooltips = $_POST['include_tooltips'];

			$view_mode = 0;
			$font = 0;
			$border_type = 0;
			if (!empty($_POST['view_mode'])) {
				$view_mode = intval($_POST['view_mode']);
			}
			if (!empty($_POST['font'])) {
				$font = intval($_POST['font']);
			}
			if (!empty($_POST['border_type'])) {
				$border_type = intval($_POST['border_type']);
			}
			// This code sets the cookies.
			if ($fromHeader) {
				setcookie("marvelsig_position_grid", $position_grid, time() + (60 * 60 * 24 * 30));
				setcookie("marvelsig_costume_grid", $costume_grid, time() + (60 * 60 * 24 * 30));
				setcookie("marvelsig_level_grid", $level_grid, time() + (60 * 60 * 24 * 30));
				setcookie("marvelsig_flair_grid", $flair_grid, time() + (60 * 60 * 24 * 30));
				$keyword = $_POST['saved_keyword'];
				if ($keyword != NULL && strlen($keyword) > 0) {
					setcookie("marvelsig_keyword", $keyword, time() + (60 * 60 * 24 * 30));
				} else {
					clearCookie("marvelsig_keyword");
				}
				$keyword = $_POST['saved_password'];
				if ($keyword != NULL && strlen($keyword) > 0) {
					setcookie("marvelsig_password", $keyword, time() + (60 * 60 * 24 * 30));
				} else {
					clearCookie("marvelsig_password");
				}
				if (!empty($view_mode)) {
					setcookie("marvelsig_view_mode", strval($view_mode), time() + (60 * 60 * 24 * 30));
				} else {
					clearCookie("marvelsig_view_mode");
				}
				if (!empty($font)) {
					setcookie("marvelsig_font", strval($font), time() + (60 * 60 * 24 * 30));
				} else {
					clearCookie("marvelsig_font");
				}
				if (!empty($border_type)) {
					setcookie("marvelsig_border_type", strval($border_type), time() + (60 * 60 * 24 * 30));
				} else {
					clearCookie("marvelsig_border_type");
				}
				if (!empty($include_link)) {
					setcookie("marvelsig_include_link", strval($include_link), time() + (60 * 60 * 24 * 30));
				} else {
					clearCookie("marvelsig_include_link");
				}
				if (!empty($include_tooltips)) {
					setcookie("marvelsig_include_tooltips", strval($include_tooltips), time() + (60 * 60 * 24 * 30));
				} else {
					clearCookie("marvelsig_include_tooltips");
				}
				return;
			}
			// Here we do password checking
			$keyword = $_POST['saved_keyword'];
			$password = $_POST['saved_password'];
			if (empty($password) || strlen($password) <= 0) {
				if (!$fromHeader) {
					showAlert("You must enter a password to save this sig.");
				}
				return;
			}
			// The passwords are salted and hashed, just for a little security.
			$password = hash("sha512", $password);
			$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, "marvelheroesdb");
			// Pull the old password from the DB
			$query = "SELECT password FROM saved_config WHERE keyword=?";
			$stmt = $mysqli -> prepare($query);
			$stmt -> bind_param("s", $keyword);
			$stmt -> execute();
			$stmt -> bind_result($saved_password);
			$stmt -> fetch();

			// Check if they match (or if the old one was empty)
			$can_save = true;
			if (empty($saved_password)) {
				$can_save = true;
			} else if ($saved_password != $password) {
				$can_save = false;
			}
			$stmt -> close();
			// Write the strings to the database; not a lot of mystery here.
			if ($can_save) {
				$query = "DELETE FROM saved_config WHERE keyword=?";
				$stmt = $mysqli -> prepare($query);
				$stmt -> bind_param("s", $keyword);
				$stmt -> execute();
				$stmt -> close();

				$query = "INSERT INTO saved_config(keyword, password, position_grid, costume_grid, level_grid, view_mode, font, include_link, include_tooltips, border_type, flair_grid) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
				$stmt = $mysqli -> prepare($query);
				$stmt -> bind_param("sssssiiiiis", $keyword, $password, $position_grid, $costume_grid, $level_grid, $view_mode, $font, $include_link, $include_tooltips, $border_type, $flair_grid);
				$stmt -> execute();
				$stmt -> close();
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