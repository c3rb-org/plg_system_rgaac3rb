<?php
defined('_JEXEC') or die;

class plgSystemRgaac3rb extends JPlugin
{
	/**
	 * @var $app
	 */
	protected $app;

	public function __construct(& $subject, $params)
	{
		parent::__construct($subject, $params);

		$this->app = JFactory::getApplication();

		$name = false;
		if ($this->app->isSite()) {
			$name = $this->params->get('sitelessc', 'lessphp-1.7.0.3');
		}else if ($this->app->isAdmin()) {
			$name = $this->params->get('adminlessc', 'lessphp-1.7.0.3');
		}

		if ($name && file_exists($file = dirname(__FILE__) . '/lessc/' . $name . '.php')) {
			require_once $file;
		}
	}

	function onAfterInitialise()
	{
		$baseLibPath = JPATH_THEMES.DIRECTORY_SEPARATOR.'c3rb_rgaa'.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'cms'.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR;

		if(file_exists($baseLibPath.'bootstrap.php')) {
			JLoader::register('JHtmlBootstrap', $baseLibPath.'bootstrap.php', true);
		}

		if(file_exists($baseLibPath.'actionsdropdown.php')) {
			JLoader::register('JHtmlActionsdropdown', $baseLibPath.'actionsdropdown.php', true);
		}

		if(file_exists($baseLibPath.'jgrid.php')) {
			JLoader::register('JHtmlGrid', $baseLibPath.'jgrid.php', true);
		}
	}

	/**
	 * Compile .less files on change
	 */
	function onBeforeRender()
	{
		//path to less file
		$lessFile = '';

		// 0 = frontend only
		// 1 = backend only
		// 2 = front + backend
		$mode = $this->params->get('mode', -1);

		//only execute frontend
		if ($this->app->isSite() && ($mode == 0 || $mode == 2))
		{
			$templatePath = JPATH_BASE . DIRECTORY_SEPARATOR . 'templates/' . $this->app->getTemplate() . DIRECTORY_SEPARATOR;

			//entrypoint for main .less file, default is less/template.less
			$lessFile = $templatePath . $this->params->get('lessfile', 'less/template.less');

			//destination .css file, default css/template.css
			$cssFile = $templatePath . $this->params->get('cssfile', 'css/template.css');

		}

		//execute backend
		if ($this->app->isAdmin() && ($mode == 1 || $mode == 2))
		{
			$templatePath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'templates/' . $this->app->getTemplate() . DIRECTORY_SEPARATOR;

			//entrypoint for main .less file, default is less/template.less
			$lessFile = $templatePath . $this->params->get('admin_lessfile', 'less/template.less');

			//destination .css file, default css/template.css
			$cssFile = $templatePath . $this->params->get('admin_cssfile', 'css/template.css');

		}

		//check if .less file exists and is readable
		if (is_readable($lessFile) && $mode != -1)
		{
			if ((bool) $this->params->get('clientside_enable', 0))
			{
				$this->clientsideLess();
			}
			else
			{
				//initialse less compiler
				try
				{
					$this->autoCompileLess($lessFile, $cssFile);
				}
				catch (Exception $e)
				{
					$this->app->enqueueMessage($e->getMessage(), 'error');
				}
			}
		}

		return false;
	}

	/**
	 * Checks if .less file has been updated and stores it in cache for quick comparison.
	 *
	 * This function is taken and modified from documentation of lessphp
	 *
	 * @param String $inputFile
	 * @param String $outputFile
	 */
	function autoCompileLess($inputFile, $outputFile)
	{
		// load config file
		$config = JFactory::getConfig();
		//path to temp folder
		$tmpPath = JPATH_CACHE;

		//load chached file
		$cacheFile = $tmpPath . DIRECTORY_SEPARATOR . $this->app->getTemplate() . "_" . basename($inputFile) . ".cache";

		if (file_exists($cacheFile))
		{
			$tmpCache = unserialize(file_get_contents($cacheFile));
			if ($tmpCache['root'] === $inputFile)
			{
				$cache = $tmpCache;
			}
			else
			{
				$cache = $inputFile;
				unlink($cacheFile);
			}
		}
		else
		{
			$cache = $inputFile;
		}

		//instantiate less compiler
		$less = new lessc3rb;

		//set less options
		//option: force recompilation regardless of change
		$force = (boolean) $this->params->get('less_force', 0);

		//option: preserve comments
		if ($this->params->get('less_comments', 0))
		{
			$less->setPreserveComments(true);
		}

		//option: compression
		if ($this->params->get('less_compress', 0))
		{
			$less->setFormatter("compressed");
		}
		else
		{
			$formatter = new lessc_formatter_classic;
			$formatter->disableSingle = true;
			$formatter->breakSelectors = true;
			$formatter->assignSeparator = ": ";
			$formatter->selectorSeparator = ",";
			$formatter->indentChar = "\t";
		}

		if((boolean) $this->params->get('less_template', 0)) {
			$lessVarParams = $this->parseTemplateParams();
			if(!empty($lessVarParams)) {
				$cacheLessVar = $tmpPath . DIRECTORY_SEPARATOR . $this->app->getTemplate() . "_less_var.cache";

				if(file_exists($cacheLessVar)) {
					$oldLessVar = unserialize(file_get_contents($cacheLessVar));
				}else {
					$oldLessVar = array();
				}

				if($lessVarParams !== $oldLessVar) {
					file_put_contents($cacheLessVar, serialize($lessVarParams));
					$cache = $inputFile; //unlink($cacheFile);
				}

				$less->setVariables($lessVarParams);
			}
		}

		//compile cache file
		$newCache = $less->cachedCompile($cache, $force);

		if(!is_array($cache) || $newCache["updated"] > $cache["updated"])
		{
			file_put_contents($cacheFile, serialize($newCache));
			file_put_contents($outputFile, $newCache['compiled']);
		}
	}

	function parseTemplateParams()
	{
		$tpl = $this->app->getTemplate(true);
		$tplParams = $tpl->params;
		$params = array();
		$pattern = 'lessvar_';

		foreach($tplParams->getIterator() as $name => $value) {
			if(strpos($name, $pattern) !== false) {
				$params[str_replace($pattern, '', strstr($name, $pattern))] = $value;
			}
		}

		return $params;
	}

	/**
	 * Configure and add Client-side Less library
	 * @author   piotr-cz
	 * @return   void
	 *
	 * @see      LESS: Ussage  http://lesscss.org/#usage
	 */
	function clientsideLess()
	{
		// Initialise variables
		$doc = JFactory::getDocument();


		// Early exit
		if ($doc->getType() !== 'html')
		{
			return;
		}

		// Get asset paths
		$templateRel = 'templates/' . $doc->template . '/';
		$templateUri = JUri::base() . $templateRel;


		// Determine which param to use (admin/ site)
		$mode = $this->params->get('mode', 0);
		$lessKey = 'lessfile';
		$cssKey = 'cssfile';

		if ($this->app->isAdmin() && ($mode == 1 || $mode == 2))
		{
			$lessKey = 'admin_' . $lessKey;
			$cssKey = 'admin_' . $cssKey;
		}


		// Get template css filenames
		$lessUri = $templateRel . $this->params->get($lessKey, 'less/template.less');
		$cssUri = $templateRel . $this->params->get($cssKey, 'css/template.css');


		// Add less file to document
		$doc->addHeadLink($lessUri, 'stylesheet/less', 'rel', array('type' => 'text/css'));

		/*
		 * Configure Less options
		 *  async			: false,
		 *  fileAsync		: false,
		 *  poll			: 1500,
		 *  relativeUrls	: false,
		 *  rootpath		: $templateUrl
		 */
		$options = array(
			'env' => 'development',
			'dumpLineNumbers' => 'mediaquery', // default: 'comments'
		);

		$doc->addScriptDeclaration('
				// Less options
				var less = ' . json_encode($options, JSON_FORCE_OBJECT | (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : false)) . ';
		');


		// Load less.js (pick latest version in media folder)
		// Joomla adds JS code after libraries in head. We need it other way around
		$mediaJsDestination = '/media/plg_rgaac3rb/js/';
		$mediaPath = JPATH_SITE . $mediaJsDestination;
		$mediaUri = JUri::root(true) . $mediaJsDestination;

		$lessVersions = glob($mediaPath . 'less-*.js');

		if (!empty($lessVersions))
		{
			rsort($lessVersions);

			// Load at the end of head
			$doc->addCustomTag('<script src="' . $mediaUri . basename($lessVersions[0]) . '" type="text/javascript"></script>');

			// Load after options (experimental, cannot use in XHTML documents)
			/*
				$doc->addScriptDeclaration('
						// Less library
						document.write( unescape( \'%3Cscript src="' . $mediaUri . basename($lessVersions[0]) . '" type="text/javascript"%3E%3C/script%3E\' ) );
				');
			*/
		}
		// Cannot find client-side parser
		else
		{
			return;
		}


		/*
		 * Remove template.css from document head
		 *
		 * Note:  Css file must be added either using `JFactory::getDocument->addStylesheet($cssFile)` or `JHtml::_('stylesheet', $cssFile)`
		 * Note:  Cannot rely on removing stylesheet using JDocumentHTML methods.
		 * Note:  Passes ignore cache trick (template.css?1234567890123)
		 * Note:  Template.css may be added to $doc['stylesheets'] using following keys:
		 *	- relative						: `templates/...`
		 *	- semi		JUri::base(true)	: `/[path-to-root]/templates/...`
		 * 	- absolute 	JUri::base()		: `http://[host]/[path-to-root]/templates/...`
		 *	- or outside $doc->_styleSheets
		 */
		$lookups = array($cssUri, JUri::base(true) . '/' . $cssUri, JUri::base() . $cssUri);

		// Loop trough all registered document stylesheets...
		foreach ($doc->_styleSheets as $stylesSheetUri => $styleSheetInfo)
		{
			// ...and compare to every lookup...
			foreach ($lookups as $lookup)
			{
				// ...that starts like a lookup
				if (strpos($stylesSheetUri, $lookup) === 0)
				{
					unset($doc->_styleSheets[$stylesSheetUri]);
					return;
				}
			}
		}

		// Didn't find a css file in JDocument instance, register event to remove in from rendered html body.
		$this->app->registerEvent('onAfterRender', array($this, 'removeCss'));

		return;
	}

	/**
	 * Remove template.css from document html
	 * Stylesheet href may include query string, ie template.css?1234567890123
	 *
	 * @return   void
	 */
	public function removeCss()
	{
		// Initialise variables
		$doc = JFactory::getDocument();
		$body = JResponse::getBody();

		// Get Uri to template stylesheet file
		$templateUri = JUri::base(true) . '/templates/' . $doc->template . '/';
		$cssUri = $templateUri . $this->params->get('cssfile', 'css/template.css');

		// Replace line with link element and path to stylesheet file
		$replaced = preg_replace( '~(\s*?<link.* href=".*?' . preg_quote($cssUri) . '(?:\?.*)?".*/>)~', '', $body, -1, $count);

		if ($count)
		{
			JResponse::setBody($replaced);
		}

		return;
	}
}
?>
