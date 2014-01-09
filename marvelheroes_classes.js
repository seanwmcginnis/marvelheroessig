function MarvelHero(char_index, home_x, home_y, grid_tag, myButton, myMenu, myLevel, myWhite, myGreen, myBlue, myPurple, myOrange, myRed, myFlair, myLevelLabel, myFlairImage, mySource, mySourceImage, myLink, myFlair2, myFlair2Image)
{
	this.char_index = char_index;
	this.home_x = home_x;
	this.home_y = home_y;
	this.myButton = myButton;
	this.myMenu = myMenu;
	this.myLevel = myLevel;
	this.myFlair = myFlair;
	this.myWhite = myWhite;
	this.myGreen = myGreen;
	this.myBlue = myBlue;
	this.myPurple = myPurple;
	this.myOrange = myOrange;
	this.myRed = myRed;
	this.level = 0;
	this.costume = 0;
	this.prestige = 0;
	this.flair = 0;
	this.flair2 = 0;
	this.source = 0;
	this.grid_tag = grid_tag;
	this.char_name = "";
	this.costume_names = null;
	this.costume_indices = null;
	this.myLevelLabel = myLevelLabel;
	this.myFlairImage = myFlairImage;
	this.mySource = mySource;
	this.mySourceImage = mySourceImage;
	this.link = "";
	this.myLink = myLink;
	this.myFlair2 = myFlair2;
	this.myFlair2Image = myFlair2Image;
}

function MarvelFlair(flair_index, flair_name, flair_file, flair_position, x_offset, y_offset)
{
	this.flair_index = flair_index;
	this.flair_name = flair_name;
	this.flair_file = flair_file;
	this.flair_position = flair_position;
	this.x_offset = x_offset;
	this.y_offset = y_offset;
}
