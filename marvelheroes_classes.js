function MarvelHero(char_index, home_x, home_y, grid_tag, myButton, myMenu, myLevel, myWhite, myGreen, myBlue, myPurple, myOrange, myRed, myFlair, myLevelLabel, myFlairImage)
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
	this.grid_tag = grid_tag;
	this.char_name = "";
	this.costume_names = null;
	this.costume_indices = null;
	this.myLevelLabel = myLevelLabel;
	this.myFlairImage = myFlairImage;
}

function MarvelFlair(flair_index, flair_name, flair_file)
{
	this.flair_index = flair_index;
	this.flair_name = flair_name;
	this.flair_file = flair_file;
}
