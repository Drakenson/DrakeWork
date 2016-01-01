<?php
class worker
{
	private $DW_Themerequest = '';
	private $DW_Filerequest = '';
	private $DW_Scriptrequest = '';

	private $DW_Title = '';
	private $DW_Construct = array();
	private $DW_Theme = array();
	private $DW_Content = '';
	private $DW_Scripts = '';
	private $DW_Filelist = array();
	private $DW_Themefiles = array();
	
	function __construct($DW_Title, $DW_Themerequest, $DW_Filerequest, $DW_Scriptrequest, $DW_Construct, $DW_Themefiles){
		$this->DW_Title = $DW_Title;
		$this->DW_Themerequest = $DW_Themerequest;	
		$this->DW_Filerequest = $DW_Filerequest;	
		$this->DW_Scriptrequest = $DW_Scriptrequest;	
		$this->DW_Construct = $DW_Construct;
		$this->DW_Themefiles = $DW_Themefiles;
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
		
		$control = $this->DW_Themefiles['control'];
		
		$parts = explode("|", $control);
		foreach ($parts as $part)
		{
			$objects = explode("&", $part);
			foreach ($objects as $object)
			{
				$section = substr($objects[0],1);
				if (!isset($this->DW_Theme[$section])) $this->DW_Theme[$section] = '';
				if ($object[0] <> "$" && $object[0] <> "%") $this->DW_Theme[$section] .= $this->DW_Themefiles[$section][$object];
				if ($object[0] == "%")
				{
					$object = substr($object,1);
					Foreach ($this->DW_Filelist as $file)
					{
						$content = $this->DW_Themefiles[$section][$object];
						$content = str_replace("[PAGE]", pathinfo($file)['filename'] , $content);
						$content = str_replace("[THEME]", "{$this->DW_Themerequest}" , $content);
						$this->DW_Theme[$section] .= $content;
					}
				}
			}
			
		}
	}
	
	private function load_basic_content() {
		$this->DW_Content = file_get_contents("content/".$this->DW_Filelist[$this->DW_Filerequest]);
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
		$this->load_theme();
		$this->load_basic_content();
		$this->load_scripts();
		$base = $this->DW_Construct['base'];
		$base = str_replace("##TITLE##", $this->DW_Title, $base);
		$base = str_replace("##SCRIPTS##", $this->DW_Scripts, $base);
		$base = str_replace("##THEME_HEAD##", $this->DW_Theme['THEME_HEAD'], $base);
		$base = str_replace("##THEME_BODY_BEFORE_CONTENT##", $this->DW_Theme['THEME_BODY_BEFORE_CONTENT'], $base);
		$base = str_replace("##CONTENT##", $this->DW_Content, $base);
		$base = str_replace("##THEME_BODY_AFTER_CONTENT##", $this->DW_Theme['THEME_BODY_AFTER_CONTENT'], $base);
		$base = $this->macros($base);
		return $base;
	}
}
?>