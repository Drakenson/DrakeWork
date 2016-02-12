<?php
class worker
{
	private $DW_Themerequest = '';
	private $DW_Filerequest = '';
	private $DW_Scriptrequest = '';

	private $DW_Title = '';
	private $DW_Construct = "<!DOCTYPE html><html><head><title>##TITLE##</title>##SCRIPTS####THEME_HEAD####CUSTOM_STYLE##</head><body>##THEME_BODY_BEFORE_CONTENT####CONTENT####THEME_BODY_AFTER_CONTENT##</body></html>";
	private $DW_Customstyle = "";
	private $DW_Filelist = array();
	
	function __construct($DW_Title, $DW_Themerequest, $DW_Filerequest, $DW_Scriptrequest, $DW_Customstyle){
		$this->DW_Title = $DW_Title;
		$this->DW_Themerequest = $DW_Themerequest;	
		$this->DW_Filerequest = $DW_Filerequest;	
		$this->DW_Scriptrequest = $DW_Scriptrequest;
		$this->DW_Customstyle = $DW_Customstyle;
		$this->DW_Filelist = $this->get_filelist();
	}
	
	private function get_filelist()	{
		$filelist = array();
		$contentfolderpath = 'content/';
		$contentfolder = openDir ($contentfolderpath); 
		while ($file = readDir($contentfolder)) 
		{
			if ($file != "." && $file != ".." && !is_dir($contentfolderpath."/".$file)) 
			{	
				$pathparts = pathinfo($file);
				$filelist[$pathparts['filename']] = $file;
			}
		}
		closeDir($contentfolder);
		return $filelist;
	}		
	private function load_scripts($scriptrequest)	{
		foreach ($scriptrequest as $script)
		{
			$scripts .= file_get_contents("scripts/{$script}/script.base");
		}
		return $scripts;
	}
	private function load_theme($themerequest, $filelist) {
		$textfile = file_get_contents("themes/{$themerequest}/variables.json");
		$theme = '';
		$config = json_decode($textfile, true);
		$control = $config['THEME_CONTROL'];
		$parts = explode("|", $control);
		foreach ($parts as $part)
		{
			$objects = explode("&", $part);
			foreach ($objects as $object)
			{
				$section = substr($objects[0],1);
				if ($object[0] <> "$" && $object[0] <> "%") $theme[$section] .= $config["$section"]["$object"];
				if ($object[0] == "%") {
					$object = substr($object,1);
					Foreach ($filelist as $file) {
						$content = $config[$section][$object];
						$content = str_replace("[PAGE]", pathinfo($file)['filename'] , $content);
						$theme[$section] .= $content;
					}
				}
			}
		}
		return $theme;
	}
	private function load_basic_content($filelist, $filerequest) {
		return file_get_contents("content/".$filelist["$filerequest"]);
	}
	private function macros($base) {
		$base = str_replace("[REMOTE_ADDR]", $_SERVER['REMOTE_ADDR'], $base);
		$base = str_replace("[HTTP_HOST]", $_SERVER["HTTP_HOST"], $base);
		$base = str_replace("[HTTP_USER_AGENT]", $_SERVER["HTTP_USER_AGENT"], $base);
		$base = str_replace("[DATE]", date("d.m.Y")	, $base);
		$base = str_replace("[TIME]", date("H:i:s")	, $base);
		$base = str_replace("[TITLE]", $this->DW_Title, $base);
		$base = str_replace("[THEME]", $this->DW_Themerequest, $base);
		$base = str_replace("[THEME_IMG]", $this->DW_Themerequest."/style", $base);
		return $base;
	}
	
	function build_site() {
		$theme = $this->load_theme($this->DW_Themerequest, $this->DW_Filelist);
		$scripts = $this->load_scripts($this->DW_Scriptrequest);
		$content = $this->load_basic_content($this->DW_Filelist, $this->DW_Filerequest);
		$base = $this->DW_Construct;
		
		$base = str_replace("##TITLE##", $this->DW_Title, $base);
		$base = str_replace("##SCRIPTS##", $scripts , $base);
		$base = str_replace("##THEME_HEAD##", $theme['THEME_HEAD'], $base);
		$base = str_replace("##CUSTOM_STYLE##", "<link rel='stylesheet' type='text/css' href='$this->DW_Customstyle'>", $base);
		$base = str_replace("##THEME_BODY_BEFORE_CONTENT##", $theme['THEME_BODY_BEFORE_CONTENT'], $base);
		$base = str_replace("##CONTENT##", $content, $base);
		$base = str_replace("##THEME_BODY_AFTER_CONTENT##", $theme['THEME_BODY_AFTER_CONTENT'], $base);
		$base = $this->macros($base);
		return $base;
	}
}
?>