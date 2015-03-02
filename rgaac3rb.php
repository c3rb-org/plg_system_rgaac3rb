<?php
defined('_JEXEC') or die;

class plgSystemRgaac3rb extends JPlugin
{
	function plgSystemRgaac3rb(&$subject, $config)
	{		
		parent::__construct($subject, $config);
	}

	function onAfterInitialise()
	{
		JLoader::register('JHtmlBootstrap', JPATH_THEMES.DIRECTORY_SEPARATOR.'rgaac3rb'.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'cms'.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'bootstrap.php', true);
	}
}
?>