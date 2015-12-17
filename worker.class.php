<?php
class worker
{
	private $DW_Title = '';
	private $DW_Theme = '';
	private $DW_Filerequest = '';
	private $DW_Content = '';
	private $DW_Scripts = '';
	private $DW_Filelist = array();
	private $DW_Scriptrequest;


	function __construct($DW_Title, $DW_Theme, $DW_Filerequest, $DW_Scriptrequest){
		$this->DW_Title = $DW_Title;
		$this->DW_Theme = $DW_Theme;	
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
			if ($file != "." && $file != "..") 
			{	
				$filelist[] = $file;
			}
		}
		closeDir($contentfolder);
		$this->DW_Filelist = $filelist;
	}		
	
	function load_scripts()	{
		foreach ($this->DW_Scriptrequest as $script)
		{
			$this->DW_Scripts .= file_get_contents("scripts/{$script}/script.base");
		}
	}
 	
	function load_theme() {
		$this->DW_Theme = file_get_contents("themes/{$this->DW_Theme}/theme.base");
	}
	
	function load_basic_content() {
		$this->DW_Content = file_get_contents("content/".$this->DW_Filelist[$this->DW_Filerequest]);
	}
	
	function load_extended_content($path, $match) {
		$path = ltrim($path, '%@');
		$match = ltrim($match, '%!');
		return file_get_contents("content/{$path}/{$match}.html");
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
		preg_match_all("/%![a-z]+/i", "$this->DW_Content", $matches);
		$file = array();
		preg_match("/%@[a-z]+/i", "$this->DW_Content", $file);
		foreach ($matches[0] as $match)
		{
			$base = str_replace($match, $this->load_extended_content(strtolower($file[0]), strtolower($match)), $base);
		}
		$base = str_replace($file[0], "", $base);
		return $base;
	}
}
?>