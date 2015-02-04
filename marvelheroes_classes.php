<?php

class MarvelFlair
{
	private $flair_index = -1;
	public function get_flair_index() {
		return $this->flair_index;
	}
	public function set_flair_index($inval) {
		$this->flair_index = $inval;
	}
	
	private $flair_name = "";
	public function get_flair_name() {
		return $this->flair_name;
	}
	public function set_flair_name($inval) {
		$this->flair_name = $inval;
	}
	
	private $flair_file = "";
	public function get_flair_file() {
		return $this->flair_file;
	}
	public function set_flair_file($inval) {
		$this->flair_file = $inval;
	}
	
	private $flair_position = 0;
	public function get_flair_position() {
		return $this->flair_position;
	}
	public function set_flair_position($inval) {
		$this->flair_position = $inval;
	}
	
	private $flair_x_offset = 0;
	public function get_flair_x_offset() {
		return $this->flair_x_offset;
	}
	public function set_flair_x_offset($inval) {
		$this->flair_x_offset = $inval;
	}
	
	private $flair_y_offset = 0;
	public function get_flair_y_offset() {
		return $this->flair_y_offset;
	}
	public function set_flair_y_offset($inval) {
		$this->flair_y_offset = $inval;
	}
}

class MarvelHero
{
    // property declaration
    private $char_index = "";
	public function get_char_index() {
		return $this->char_index;
	}
	public function set_char_index($inval) {
		$this->char_index = $inval;
	}
	
	private $cos_indices = array();
	public function get_cos_indices_count()
	{
		return count($this->cos_indices);
	}
	
	public function push_cos_indices($inval)
	{
		$this->cos_indices[] = $inval;
	}
	
	public function pop_cos_indices($i)
	{
		if($i >= count($this->cos_names))
		{
			return null;
		}
		return $this->cos_indices[$i];
	}
	
	private $cos_images = array();
	public function get_cos_images_count()
	{
		return count($this->cos_images);
	}
	
	public function push_cos_images($inval)
	{
		$this->cos_images[] = $inval;
	}
	
	public function pop_cos_images($i)
	{
		if($i >= count($this->cos_images))
		{
			return null;
		}
		return $this->cos_images[$i];
	}
	
	private $cos_names = array();
	public function get_cos_names_count()
	{
		return count($this->cos_names);
	}
	
	public function push_cos_names($inval)
	{
		$this->cos_names[] = $inval;
	}
	
	public function pop_cos_names($i)
	{
		if($i >= count($this->cos_names))
		{
			return null;
		}
		return $this->cos_names[$i];
	}
	
	private $char_name = "";
	public function get_char_name() {
		return $this->char_name;
	}
	public function set_char_name($inval) {
		$this->char_name = $inval;
	}
	
	private $home_x = 0;
	public function get_home_x()
	{
		return $this->home_x;
	}
	
	public function set_home_x($inval)
	{
		$this->home_x = $inval;
	}
	
	private $home_y = 0;
	public function get_home_y()
	{
		return $this->home_y;
	}
	
	public function set_home_y($inval)
	{
		$this->home_y = $inval;
	}
	
	private $display_order = 0;
	public function get_display_order()
	{
		return $this->display_order;
	}
	
	public function set_display_order($inval)
	{
		$this->display_order = $inval;
	}
	
	private $flair = -1;
	public function get_flair()
	{
		return $this->flair;
	}
	
	public function set_flair($inval)
	{
		$this->flair = $inval;
	}
}

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

function convertStringWithPadding($str, $old_token_length, $new_token_length) {
	$ret = "";
	$index = 0;
	while ($index < strlen($str)) {
		$token = substr($str, $index, $old_token_length);
		$ret = $ret . str_pad($token, $new_token_length, "0", STR_PAD_LEFT);
		$index += $old_token_length;
	}
	return $ret;
}

function convertLegacyFlairString($str, $version, $num_chars)
{
	if($version >= 2)
	{
		return $str;
	}
	$num_in_field = 0;
	if($version == 0)
	{
		$str = convertStringWithPadding($str, 2, 3);
		$num_in_field = strlen($str)/6;
	}
	if($version == 1)
	{
		$num_in_field = strlen($str)/9;
	}
	$ret = "";
	for($i=0; $i < $num_chars; $i++)
	{
		if($i >= $num_in_field)	
		{
			$ret = $ret . "000000000000";
		}
		else {
			$ret = $ret . getGridValue($str, $i, 3) . getGridValue($str, $num_in_field + $i, 3);
			if($version == 1)
			{
				$ret = $ret . getGridValue($str, (2 * $num_in_field) + $i, 3) . "000";
			}
			else
			{
				$ret = $ret . "000000";
			}
		}
	}
	return $ret;
}
?>