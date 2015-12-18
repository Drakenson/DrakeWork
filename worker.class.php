<?php
class worker
{
	private $DW_Themerequest = '';
	private $DW_Filerequest = '';
	private $DW_Scriptrequest = '';

	private $DW_Title = '';
	private $DW_Theme = '';
	private $DW_Content = '';
	private $DW_Scripts = '';
	private $DW_Filelist = array();
	


	function __construct($DW_Title, $DW_Themerequest, $DW_Filerequest, $DW_Scriptrequest){
		$this->DW_Title = $DW_Title;
		$this->DW_Themerequest = $DW_Themerequest;	
		$this->DW_Filerequest = $DW_Filerequest;	
		$this->DW_Scriptrequest = $DW_Scriptrequest;	
		$this->get_filelist();
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
		$this->DW_Filelist = $filelist;
	}		
	
	private function load_scripts()	{
		foreach ($this->DW_Scriptrequest as $script)
		{
			$this->DW_Scripts .= file_get_contents("scripts/{$script}/script.base");
		}
	}
 	
	private function load_theme() {
		$this->DW_Theme = file_get_contents("themes/{$this->DW_Themerequest}/theme.base");
	}
	
	private function load_basic_content() {
		$this->DW_Content = file_get_contents("content/".$this->DW_Filelist[$this->DW_Filerequest]);
	}
	
	private function load_extended_content($path, $match) {
		$path = ltrim($path, '%@');
		$match = ltrim($match, '%!');
		return file_get_contents("content/{$path}/{$match}.html");
	}
	
	private function macros($base) {
		
		$base = str_replace("%?REMOTE_ADDR", $_SERVER['REMOTE_ADDR'], $base);
		$base = str_replace("%?HTTP_HOST", $_SERVER["HTTP_HOST"], $base);
		$base = str_replace("%?HTTP_USER_AGENT", $_SERVER["HTTP_USER_AGENT"], $base);
		$base = str_replace("%?DATE", date("d.m.Y")	, $base);
		$base = str_replace("%?TIME", date("H:i:s")	, $base);
		
		return $base;
	}
	
	function build_site() {
		$this->load_theme();
		$this->load_basic_content();
		$this->load_scripts();
		$base = file_get_contents("construct/main.base");
		$base = str_replace("##TITLE##", $this->DW_Title, $base);
		$base = str_replace("##SCRIPTS##", $this->DW_Scripts, $base);
		$base = str_replace("##THEME##", $this->DW_Theme, $base);
		$base = str_replace("##CONTENT##", $this->DW_Content, $base);

		$matches = array();
		$file = array();
		preg_match_all("/%![a-z]+/i", "$this->DW_Content", $matches);
		preg_match("/%@[a-z]+/i", "$this->DW_Content", $file);
		foreach ($matches[0] as $match)	{$base = str_replace($match, $this->load_extended_content(strtolower($file[0]), strtolower($match)), $base);};
		$base = str_replace($file[0], "", $base);

		$base = str_replace("%&THEME", $this->DW_Themerequest, $base);
		$base = $this->macros($base);
		return $base;
	}
}
?>