<?php
	/**
	 * Created by PhpStorm.
	 * User: denmedia
	 * Date: 08.04.2015
	 * Time: 23:20
	 */


	function hiweb() {
		static $hiweb = null;
		if( is_null( $hiweb ) ) $hiweb = new hiweb();

		return $hiweb;
	}


	class hiweb {

		public $globalValues = array();

		public $debugMod = false;

		private $_cache = array();
		private $_cacheEnable = true;
		private $_cacheByFile = null;
		private $_cacheByFileEnable = true;
		private $_cacheByFileName = 'cacheByFile.json';

		function __construct() {
			if( defined( 'HIWEB_DIR_CACHE' ) && !file_exists( HIWEB_DIR_CACHE ) ) mkdir( HIWEB_DIR_CACHE, 0644 );
		}


		////////////////////////////////////////////////////

		/**
		 * Подключение класса FILE
		 *
		 * @return hiweb_file
		 */
		public function file() { return $this->connect(); }


		/**
		 * Подключение класса STRING
		 *
		 * @return hiweb_string
		 */
		public function string() { return $this->connect(); }


		/**
		 * Подключение класса INPUT
		 *
		 * @return hiweb_input
		 */
		public function input() { return $this->connect(); }


		/**
		 * Подключение класса ARRAY
		 *
		 * @return hiweb_array
		 */
		public function array2() {
			static $class = null;
			if( !is_object( $class ) && file_exists( dirname( __FILE__ ) . '/hiweb-core-array.php' ) ) {
				require_once dirname( __FILE__ ) . '/hiweb-core-array.php';
				$class = new hiweb_array();
			}

			return $class;
		}

		/**
		 * Подключение класса CURL
		 *
		 * @return hiweb_curl
		 */
		//TODO Слить вместе с HIWEB_URL
		public function curl() { return $this->connect(); }

		/**
		 * Возвращает класс Plugins
		 *
		 * @return hiweb_plugins
		 */
		//TODO Отправить в HIWEB_CMS
		public function plugins() { return $this->connect(); }

		/**
		 * Возвращает класс SETTINGS
		 *
		 * @return hiweb_settings
		 */
		public function settings() { return $this->connect(); }

		/**
		 * Возвращает класс WP
		 *
		 * @return hiweb_wp
		 */
		public function wp() { return $this->connect(); }


		/**
		 * Подключение модуля функций hiweb_tpl
		 *
		 * @return hiweb_tpl
		 */
		public function tpl() {
			static $class = null;
			if( !is_object( $class ) && file_exists( dirname( __FILE__ ) . '/hiweb-core-tpl.php' ) ) {
				require_once dirname( __FILE__ ) . '/hiweb-core-tpl.php';
				$class = new hiweb_tpl();
				$class->registerPlugin( 'block', 'lang', array( $this, 'tplBlock_lang' ) );
				$class->registerPlugin( 'modifier', 'lang', array( $this, 'tplModifier_lang' ) );
				$class->registerPlugin( 'modifier', 'allow', array( $this->string(), 'getStr_allowSymbols' ) );
				$class->registerPlugin( 'modifier', 'print_r', array( $this, 'print_r' ) );
				$class->registerPlugin( 'modifier', 'tpl', array( $this->file(), 'getHtml_fromTplStr' ) );
				$class->registerPlugin( 'block', 'helpPoint', array( $this->wizard(), 'getHtml_helpPoint' ) );
				$class->registerPlugin( 'block', 'helpPointImage', array( $this->wizard(), 'getHtml_helpPointImage' ) );
				$cachePath = HIWEB_DIR_CACHE . DIR_SEPARATOR . 'tpl';
				$this->file()->do_foldersAutoCreate( $cachePath );
				$class->template_dir = $cachePath . "/templates";
				$class->compile_dir = $cachePath . "/templates_c";
				$class->config_dir = $cachePath . "/config";
				$class->cache_dir = $cachePath . "/cache";
				//$smarty->plugins_dir[]  = $theme_path . "/plugins";
				//$smarty->trusted_dir  = $theme_path . "/trusted";
			}

			return $class;
		}


		public function lang( $content ) {
			return __( __( $content, 'hiweb-core' ) );
		}

		public function tplBlock_lang( $params = null, $content = null, $smarty = null, &$repeat = null, $template = null ) {
			return $this->lang( $content );
		}

		public function tplModifier_lang( $content = null ) {
			return $this->lang( $content );
		}

		/**
		 * Подключение модуля функций SimpleHTMLDom
		 *
		 * @return hiweb_html_base
		 */
		public function html() { return $this->connect(); }


		/**
		 * Подключение модуля error
		 *
		 * @param bool $showBacktrace - показать более подробную информацию об ошибке
		 *
		 * @return hiweb_error
		 */
		public function error( $showBacktrace = false ) {
			static $class = null;
			if( !is_object( $class ) && file_exists( dirname( __FILE__ ) . '/hiweb-core-error.php' ) ) {
				require_once dirname( __FILE__ ) . '/hiweb-core-error.php';
				$class = new hiweb_error( $showBacktrace );
			}

			return $class;
		}


		/**
		 * @param null $info
		 *
		 * @return hiweb_console
		 */
		public function console( $info = null ) {
			static $class = null;
			if( !is_object( $class ) && file_exists( dirname( __FILE__ ) . '/hiweb-core-console.php' ) ) {
				require_once dirname( __FILE__ ) . '/hiweb-core-console.php';
				$class = new hiweb_console( $info );
			} elseif( is_object( $class ) && !is_null( $info ) ) {
				$class->info( $info );
			}

			return $class;
		}


		/**
		 * @return hiweb_cpt
		 */
		//TODO Перенести в HIWEB_CMS
		public function cpt() {
			return $this->connect();
		}


		/**
		 * @return hiweb_cron
		 */
		public function cron() {
			return $this->connect( 'cron' );
		}

		/**
		 * @return hiweb_wp_settings
		 */
		//TODO Перенести в HIWEB_CMS
		public function wp_settings() {
			return $this->connect();
		}


		/**
		 * @return hiweb_wizard
		 */
		public function wizard() {
			return $this->connect();
		}


		/**
		 * @return hiweb_build
		 */
		public function build() {
			return $this->connect();
		}


		/**
		 * @return hiweb_url
		 */
		public function url() {
			return $this->connect();
		}


		/**
		 * Подключить корневой класс hiweb
		 *
		 * @param null $className
		 * @param bool $newInstance
		 *
		 * @return mixed
		 *
		 * @version 1.2
		 */
		private function connect( $className = null, $newInstance = false ) {
			if( is_null( $className ) || empty( $className ) ) {
				$className = hiweb()->array2()->getVal( $this->getArr_debugBacktrace( 0, 1, 0, 0, 0, 0, 3, 3 ), 0, 'class' );
			}
			if( $newInstance ) {
				$php = dirname( __FILE__ ) . '/hiweb-core-' . $className . '.php';
				if( file_exists( $php ) ) {
					require_once $php;
					$className = 'hiweb_' . $className;

					return new $className();
				} else {
					hiweb()->console()->error( 'файл [' . $php . '] не найден.', 1 );

					return null;
				}
			} else {
				static $class = array();
				$php = dirname( __FILE__ ) . '/hiweb-core-' . $className . '.php';
				$className = 'hiweb_' . $className;
				if( !is_object( $this->array2()->getVal( $class, $className, null ) ) && file_exists( $php ) ) {
					require_once $php;
					$class[ $className ] = new $className();
				} elseif( !is_object( $this->array2()->getVal( $class, $className, null ) ) && !file_exists( $php ) ) {
					hiweb()->console()->error( 'файл или класс [' . $className . '] не найден!', 1 );

					return null;
				}

				return $this->array2()->getVal( $class, $className, null );
			}
		}


		//////////////////////////////////////////////////////


		/**
		 * Возвращает значение $_REQUEST
		 *
		 * @param      $keyMix - ключ или глубинный массив ключей
		 * @param null $def    - вернуть данное значение, если ключ(-и) не найдены
		 *
		 * @return mixed
		 */
		public function request( $keyMix, $def = null ) {
			return $this->array2()->getVal( $_REQUEST, $keyMix, $def );
		}


		/**
		 * @param array|object         $haystack - целевой массив
		 * @param string|integer|array $keyMix   - ключ (массив вложенных ключей) в целевом массиве
		 * @param mixed                $def      - вернуть значение, если значение не найдено
		 *
		 * @return mixed
		 */
		public function getVal_fromArr( $haystack = array(), $keyMix = '', $def = null ) { return $this->array2()->getVal( $haystack, $keyMix, $def ); }


		/**
		 * Включить режим DEBUG
		 *
		 * @param bool $showBacktrace
		 *
		 * @version 1.4
		 */
		public function debug( $showBacktrace = false ) {
			$this->debugMod = true;
			$this->error( $showBacktrace );
			$debugBacktrace = debug_backtrace();
			$this->console()->warn( 'hiweb->debug включен : ' . $this->file()->getStr_linkPath( $this->array2()->getVal( $debugBacktrace, array( 0, 'file' ) ) ) . ' : line:' . $this->array2()->getVal( $debugBacktrace, array(
					0,
					'line'
				) ) );
		}

		/**
		 * Вывести содержимое переменной
		 *
		 * @param      $mixed - переменная
		 * @param int  $delph - глубина массивов и объектов
		 * @param bool $echo  - TRUE -> вывест  на экран, FALSE -> вернуть строку
		 *
		 * @return string | void
		 * @version 1.2
		 */
		public function print_r( $mixed, $delph = 10, $echo = true ) {
			$r = $this->string()->getHtml_arrayPrint( $mixed, $delph );
			if( $echo ) echo $r; else return $r;
		}


		/**
		 * Возвращает TRUE, если указать функцию
		 *
		 * @param $functionVariable - переменная на проверку типа "функция"
		 *
		 * @return bool
		 */
		public function is_function( $functionVariable ) {
			return ( is_string( $functionVariable ) && function_exists( $functionVariable ) ) || ( is_object( $functionVariable ) && ( $functionVariable instanceof Closure ) );
		}

		/**
		 * Возвращает TRUE, если указать функцию
		 *
		 * @param $functionVariable - переменная на проверку типа "функция"
		 *
		 * @return bool
		 */
		public function getBool_isFunction( $functionVariable ) { return hiweb::is_function( $functionVariable ); }


		/**
		 * Выводит JavaScript, заставляющий браузер выполнить редирект на указанный URL, или корневой URL сайта
		 *
		 * @param string $url
		 * @param bool   $permanent
		 */
		public function do_redirect( $url = '', $permanent = true ) {
			if( trim( (string)$url ) == '' ) $url = BASE_URL;
			header( "Location: $url", true, $permanent ? 301 : 302 );
			?>redirect to: <b><?php echo $url; ?></b>
			<script>self.location = "<?php echo $url; ?>";
				window.location.href = "<?php echo $url; ?>";
				document.location.href = '<?php echo $url; ?>';
				window.location.replace("<?php echo $url; ?>");</script><?php
			die();
		}


		/**
		 * Возвращает массив информации о браузере и прочем...
		 *
		 * @return array
		 */
		public function getArr_clientInfo() {
			if( isset( $_SESSION[ 'client' ] ) && is_array( $_SESSION[ 'client' ] ) && count( $_SESSION[ 'client' ] ) > 0 ) {
				return $_SESSION[ 'client' ];
			}
			if( !isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) ) {
				return;
			}
			$u_agent = $_SERVER[ 'HTTP_USER_AGENT' ];
			$bname = '';
			$platform = '';
			$version = "";
			///
			if( preg_match( '/linux/i', $u_agent ) ) {
				$platform = 'linux';
			} elseif( preg_match( '/macintosh|mac os x/i', $u_agent ) ) {
				$platform = 'mac';
			} elseif( preg_match( '/windows|win32/i', $u_agent ) ) {
				$platform = 'windows';
			}
			$ub = '';
			if( preg_match( '/MSIE/i', $u_agent ) && !preg_match( '/Opera/i', $u_agent ) ) {
				$bname = 'Internet Explorer';
				$ub = "msie";
			} elseif( preg_match( '/Firefox/i', $u_agent ) ) {
				$bname = 'Mozilla Firefox';
				$ub = "firefox";
			} elseif( preg_match( '/Chrome/i', $u_agent ) ) {
				$bname = 'Google Chrome';
				$ub = "chrome";
			} elseif( preg_match( '/Safari/i', $u_agent ) ) {
				$bname = 'Apple Safari';
				$ub = "safari";
			} elseif( preg_match( '/Opera/i', $u_agent ) ) {
				$bname = 'Opera';
				$ub = "opera";
			} elseif( preg_match( '/Netscape/i', $u_agent ) ) {
				$bname = 'Netscape';
				$ub = "netscape";
			}
			$known = array( 'Version', $ub, 'other' );
			$pattern = '#(?<browser>' . join( '|', $known ) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
			if( !preg_match_all( $pattern, strtolower( $u_agent ), $matches ) ) {
			}
			$i = count( $matches[ 'browser' ] );
			if( $i != 1 ) {
				if( strripos( $u_agent, "Version" ) < strripos( $u_agent, $ub ) ) {
					$version = isset( $matches[ 'version' ][ 0 ] ) ? $matches[ 'version' ][ 0 ] : $matches[ 'version' ];
				} else {
					$version = $matches[ 'version' ][ 1 ];
				}
			} else {
				$version = $matches[ 'version' ][ 0 ];
			}
			if( $version == null || $version == "" ) {
				$version = "?";
			}
			/////
			$_SESSION[ 'client' ][ 'ip' ] = $_SERVER[ 'REMOTE_ADDR' ];
			$_SESSION[ 'client' ][ 'browser' ] = $ub;
			$_SESSION[ 'client' ][ 'version' ] = $version;
			$_SESSION[ 'client' ][ 'name' ] = $bname;
			$_SESSION[ 'client' ][ 'comm' ] = $u_agent;
			$_SESSION[ 'client' ][ 'mobile' ] = $this->getBool_mobileBrowser();
			$_SESSION[ 'client' ][ 'platform' ] = $platform;

			///
			return $_SESSION[ 'client' ];
		}

		/**
		 * Возвращает TRUR, если использован мобильный браузер
		 *
		 * @return bool|int
		 */
		public function getBool_mobileBrowser() {
			if( isset( $_SESSION[ 'client' ][ 'mobile' ] ) ) {
				return $_SESSION[ 'client' ][ 'mobile' ];
			}
			$user_agent = strtolower( getenv( 'HTTP_USER_AGENT' ) );
			$accept = strtolower( getenv( 'HTTP_ACCEPT' ) );

			if( ( strpos( $accept, 'text/vnd.wap.wml' ) !== false ) || ( strpos( $accept, 'application/vnd.wap.xhtml+xml' ) !== false ) ) {
				return 1; // Мобильный браузер обнаружен по HTTP-заголовкам
			}

			if( isset( $_SERVER[ 'HTTP_X_WAP_PROFILE' ] ) || isset( $_SERVER[ 'HTTP_PROFILE' ] ) ) {
				return 2; // Мобильный браузер обнаружен по установкам сервера
			}

			if( preg_match( '/(mini 9.5|vx1000|lge |m800|e860|u940|ux840|compal|' . 'wireless| mobi|ahong|lg380|lgku|lgu900|lg210|lg47|lg920|lg840|' . 'lg370|sam-r|mg50|s55|g83|t66|vx400|mk99|d615|d763|el370|sl900|' . 'mp500|samu3|samu4|vx10|xda_|samu5|samu6|samu7|samu9|a615|b832|' . 'm881|s920|n210|s700|c-810|_h797|mob-x|sk16d|848b|mowser|s580|' . 'r800|471x|v120|rim8|c500foma:|160x|x160|480x|x640|t503|w839|' . 'i250|sprint|w398samr810|m5252|c7100|mt126|x225|s5330|s820|' . 'htil-g1|fly v71|s302|-x113|novarra|k610i|-three|8325rc|8352rc|' . 'sanyo|vx54|c888|nx250|n120|mtk |c5588|s710|t880|c5005|i;458x|' . 'p404i|s210|c5100|teleca|s940|c500|s590|foma|samsu|vx8|vx9|a1000|' . '_mms|myx|a700|gu1100|bc831|e300|ems100|me701|me702m-three|sd588|' . 's800|8325rc|ac831|mw200|brew |d88|htc\/|htc_touch|355x|m50|km100|' . 'd736|p-9521|telco|sl74|ktouch|m4u\/|me702|8325rc|kddi|phone|lg |' . 'sonyericsson|samsung|240x|x320vx10|nokia|sony cmd|motorola|' . 'up.browser|up.link|mmp|symbian|smartphone|midp|wap|vodafone|o2|' . 'pocket|kindle|mobile|psp|treo|android|iphone|ipod|webos|wp7|wp8|' . 'fennec|blackberry|htc_|opera m|windowsphone)/', $user_agent ) ) {
				return 3; // Мобильный браузер обнаружен по сигнатуре User Agent
			}

			if( in_array( substr( $user_agent, 0, 4 ), Array(
					"1207",
					"3gso",
					"4thp",
					"501i",
					"502i",
					"503i",
					"504i",
					"505i",
					"506i",
					"6310",
					"6590",
					"770s",
					"802s",
					"a wa",
					"abac",
					"acer",
					"acoo",
					"acs-",
					"aiko",
					"airn",
					"alav",
					"alca",
					"alco",
					"amoi",
					"anex",
					"anny",
					"anyw",
					"aptu",
					"arch",
					"argo",
					"aste",
					"asus",
					"attw",
					"au-m",
					"audi",
					"aur ",
					"aus ",
					"avan",
					"beck",
					"bell",
					"benq",
					"bilb",
					"bird",
					"blac",
					"blaz",
					"brew",
					"brvw",
					"bumb",
					"bw-n",
					"bw-u",
					"c55/",
					"capi",
					"ccwa",
					"cdm-",
					"cell",
					"chtm",
					"cldc",
					"cmd-",
					"cond",
					"craw",
					"dait",
					"dall",
					"dang",
					"dbte",
					"dc-s",
					"devi",
					"dica",
					"dmob",
					"doco",
					"dopo",
					"ds-d",
					"ds12",
					"el49",
					"elai",
					"eml2",
					"emul",
					"eric",
					"erk0",
					"esl8",
					"ez40",
					"ez60",
					"ez70",
					"ezos",
					"ezwa",
					"ezze",
					"fake",
					"fetc",
					"fly-",
					"fly_",
					"g-mo",
					"g1 u",
					"g560",
					"gene",
					"gf-5",
					"go.w",
					"good",
					"grad",
					"grun",
					"haie",
					"hcit",
					"hd-m",
					"hd-p",
					"hd-t",
					"hei-",
					"hiba",
					"hipt",
					"hita",
					"hp i",
					"hpip",
					"hs-c",
					"htc ",
					"htc-",
					"htc_",
					"htca",
					"htcg",
					"htcp",
					"htcs",
					"htct",
					"http",
					"huaw",
					"hutc",
					"i-20",
					"i-go",
					"i-ma",
					"i230",
					"iac",
					"iac-",
					"iac/",
					"ibro",
					"idea",
					"ig01",
					"ikom",
					"im1k",
					"inno",
					"ipaq",
					"iris",
					"jata",
					"java",
					"jbro",
					"jemu",
					"jigs",
					"kddi",
					"keji",
					"kgt",
					"kgt/",
					"klon",
					"kpt ",
					"kwc-",
					"kyoc",
					"kyok",
					"leno",
					"lexi",
					"lg g",
					"lg-a",
					"lg-b",
					"lg-c",
					"lg-d",
					"lg-f",
					"lg-g",
					"lg-k",
					"lg-l",
					"lg-m",
					"lg-o",
					"lg-p",
					"lg-s",
					"lg-t",
					"lg-u",
					"lg-w",
					"lg/k",
					"lg/l",
					"lg/u",
					"lg50",
					"lg54",
					"lge-",
					"lge/",
					"libw",
					"lynx",
					"m-cr",
					"m1-w",
					"m3ga",
					"m50/",
					"mate",
					"maui",
					"maxo",
					"mc01",
					"mc21",
					"mcca",
					"medi",
					"merc",
					"meri",
					"midp",
					"mio8",
					"mioa",
					"mits",
					"mmef",
					"mo01",
					"mo02",
					"mobi",
					"mode",
					"modo",
					"mot ",
					"mot-",
					"moto",
					"motv",
					"mozz",
					"mt50",
					"mtp1",
					"mtv ",
					"mwbp",
					"mywa",
					"n100",
					"n101",
					"n102",
					"n202",
					"n203",
					"n300",
					"n302",
					"n500",
					"n502",
					"n505",
					"n700",
					"n701",
					"n710",
					"nec-",
					"nem-",
					"neon",
					"netf",
					"newg",
					"newt",
					"nok6",
					"noki",
					"nzph",
					"o2 x",
					"o2-x",
					"o2im",
					"opti",
					"opwv",
					"oran",
					"owg1",
					"p800",
					"palm",
					"pana",
					"pand",
					"pant",
					"pdxg",
					"pg-1",
					"pg-2",
					"pg-3",
					"pg-6",
					"pg-8",
					"pg-c",
					"pg13",
					"phil",
					"pire",
					"play",
					"pluc",
					"pn-2",
					"pock",
					"port",
					"pose",
					"prox",
					"psio",
					"pt-g",
					"qa-a",
					"qc-2",
					"qc-3",
					"qc-5",
					"qc-7",
					"qc07",
					"qc12",
					"qc21",
					"qc32",
					"qc60",
					"qci-",
					"qtek",
					"qwap",
					"r380",
					"r600",
					"raks",
					"rim9",
					"rove",
					"rozo",
					"s55/",
					"sage",
					"sama",
					"samm",
					"sams",
					"sany",
					"sava",
					"sc01",
					"sch-",
					"scoo",
					"scp-",
					"sdk/",
					"se47",
					"sec-",
					"sec0",
					"sec1",
					"semc",
					"send",
					"seri",
					"sgh-",
					"shar",
					"sie-",
					"siem",
					"sk-0",
					"sl45",
					"slid",
					"smal",
					"smar",
					"smb3",
					"smit",
					"smt5",
					"soft",
					"sony",
					"sp01",
					"sph-",
					"spv ",
					"spv-",
					"sy01",
					"symb",
					"t-mo",
					"t218",
					"t250",
					"t600",
					"t610",
					"t618",
					"tagt",
					"talk",
					"tcl-",
					"tdg-",
					"teli",
					"telm",
					"tim-",
					"topl",
					"tosh",
					"treo",
					"ts70",
					"tsm-",
					"tsm3",
					"tsm5",
					"tx-9",
					"up.b",
					"upg1",
					"upsi",
					"utst",
					"v400",
					"v750",
					"veri",
					"virg",
					"vite",
					"vk-v",
					"vk40",
					"vk50",
					"vk52",
					"vk53",
					"vm40",
					"voda",
					"vulc",
					"vx52",
					"vx53",
					"vx60",
					"vx61",
					"vx70",
					"vx80",
					"vx81",
					"vx83",
					"vx85",
					"vx98",
					"w3c ",
					"w3c-",
					"wap-",
					"wapa",
					"wapi",
					"wapj",
					"wapm",
					"wapp",
					"wapr",
					"waps",
					"wapt",
					"wapu",
					"wapv",
					"wapy",
					"webc",
					"whit",
					"wig ",
					"winc",
					"winw",
					"wmlb",
					"wonu",
					"x700",
					"xda-",
					"xda2",
					"xdag",
					"yas-",
					"your",
					"zeto",
					"zte-"
				) ) ) {
				return 4; // Мобильный браузер обнаружен по сигнатуре User Agent
			}

			return false; // Мобильный браузер не обнаружен
		}


		/**
		 * Возвращает массив
		 *
		 * @param bool   $class            - возвращать классы className
		 * @param bool   $functions        - возвращать имена функций functionName
		 * @param bool   $files            - возвращать имена файлов fileName
		 * @param bool   $dirs             - возвращать имена папки, из которой вызван файл
		 * @param bool   $paths            - возвращать путь папки с файлом
		 * @param bool   $returnChunkArray - возвращать разбитый массив на ключевые значения array('class' => ..., 'functions' => ..., 'file' => ...)
		 * @param int    $minDepth         - минимальная глубина
		 * @param int    $maxDepth         - максимальная глубина
		 * @param string $prepend          - добавлять до значения
		 * @param string $append           - добавлять после каждого значения
		 * @param bool   $args             - возвращать аргументы
		 *
		 * @return array
		 *
		 * @version 1.2
		 */
		public function getArr_debugBacktrace( $class = true, $functions = true, $files = true, $dirs = true, $paths = true, $returnChunkArray = false, $minDepth = 2, $maxDepth = 4, $prepend = '', $append = '', $args = false ) {
			$r = array();
			$dbt = debug_backtrace();
			$n = 0;
			foreach( $dbt as $i ) {
				$n++;
				if( $n < $minDepth || $n > $maxDepth ) continue;
				if( $class && isset( $i[ 'class' ] ) ) $r[ 'className' ][] = $prepend . $i[ 'class' ] . $append;
				if( $args && isset( $i[ 'args' ] ) ) $r[ 'args' ][] = $i[ 'args' ];
				if( $functions && isset( $i[ 'function' ] ) ) $r[ 'functionName' ][] = $prepend . $i[ 'function' ] . $append;
				if( $files && isset( $i[ 'file' ] ) ) $r[ 'fileName' ][] = $prepend . $this->file()->getArr_fileData( $i[ 'file' ] )->filename . $append;
				if( $dirs && isset( $i[ 'file' ] ) ) {
					$r[ 'dirName' ][] = $prepend . basename( dirname( $i[ 'file' ] ) ) . $append;
					if( $n < $maxDepth ) $r[ 'dirName' ][] = $prepend . basename( dirname( dirname( $i[ 'file' ] ) ) ) . $append;
				}
				if( $paths && isset( $i[ 'file' ] ) ) {
					$r[ 'path' ][] = hiweb()->file()->getStr_normalizeDirSeparates( $prepend . dirname( $i[ 'file' ] ) . $append );
					if( $n < $maxDepth ) $r[ 'path' ][] = hiweb()->file()->getStr_normalizeDirSeparates( $prepend . dirname( dirname( $i[ 'file' ] ) ) . $append );
				}
			}
			foreach( $r as $k => $i ) {
				$r[ $k ] = array_unique( $i );
			}
			if( !$returnChunkArray ) {
				$r2 = array();
				foreach( $r as $i ) {
					$r2 = hiweb()->array2()->merge( $r2, $i );
				}

				return $r2;
			}

			return $r;
		}


		/**
		 * Возвращает путь и строку файла, откуда была запущена функция
		 *
		 * @param int $depth - глубина родительских функций
		 *
		 * @return string
		 *
		 * @version 1.1
		 */
		public function getStr_debugBacktraceFunctionLocate( $depth = 1 ) {
			$debugBacktrace = debug_backtrace();
			if( hiweb()->array2()->count( $debugBacktrace ) < $depth ) {
				hiweb()->console()->warn( 'Слишком глубоко [' . $depth . ']', 1 );
			} else return $this->file()->getStr_linkPath( $this->array2()->getVal( $debugBacktrace, array( $depth, 'file' ), ':файл не найден:' ) ) . ' : ' . $this->array2()->getVal( $debugBacktrace, array( $depth, 'line' ) );
		}

		/**
		 * Возвращает функцию, откуда была запущена текущая функция
		 *
		 * @param int $depth
		 *
		 * @return string
		 */
		public function getStr_debugBacktraceFunctionTrace( $depth = 1 ) {
			$debugBacktrace = debug_backtrace();
			$class = $this->array2()->getVal( $debugBacktrace, array( $depth, 'class' ), '' );
			$function = $this->array2()->getVal( $debugBacktrace, array( $depth, 'function' ), '' );
			$type = $this->array2()->getVal( $debugBacktrace, array( $depth, 'type' ), '' );
			//Class filter
			if( strpos( $class, 'hiweb_' ) === 0 && method_exists( hiweb(), substr( $class, 6 ) ) ) $r = 'hiweb->' . substr( $class, 6 ); else $r = $class;
			$r .= $type . $function;

			return $r;
		}


		/**
		 * Возвращает корневую папку сайта. Данная функция автоматически определяет корневую папку сайта, отталкиваясь на поиске папок с файлом index.php
		 *
		 * @return string
		 *
		 * @version 1.2
		 */
		public function getStr_baseDir() {
			if( $this->cacheExists() ) return $this->cache();
			$dirs = array( $this->file()->getStr_normalizeDirSeparates( dirname( $_SERVER[ 'DOCUMENT_ROOT' ] ), 1 ) );
			foreach( explode( DIR_SEPARATOR, str_replace( $this->file()->getStr_normalizeDirSeparates( dirname( $_SERVER[ 'DOCUMENT_ROOT' ] ) ), '', $this->file()->getStr_normalizeDirSeparates( dirname( $_SERVER[ 'SCRIPT_FILENAME' ] ) ) ) ) as $dir ) {
				if( trim( $dir ) == '' ) continue;
				$dirs[] = end( $dirs ) . DIR_SEPARATOR . $dir;
			}
			$r = '';
			foreach( array_reverse( $dirs ) as $dir ) {
				if( @is_readable( $dir . DIR_SEPARATOR . '/wp-config.php' ) ) return $dir;
				if( basename( $dir ) == $_SERVER[ 'HTTP_HOST' ] ) return $dir;
				if( !@is_readable( $dir . DIR_SEPARATOR . 'index.php' ) || !is_file( $dir . DIR_SEPARATOR . 'index.php' ) ) return $r;
				$r = $dir;
			}

			return $this->cache( $r );
		}

		/**
		 * Возвращает корневой URL сайта, включая тот факт, если сайт лежим в подпапке или на домене 3 уровня
		 *
		 * @return string
		 *
		 * @version 1.1
		 */
		public function getStr_baseUrl() {
			return $this->url()->getStr_baseUrl();
		}

		/**
		 * Возвращает запрашиваемый URL, относительно корневого URL
		 *
		 * @return mixed
		 */
		public function getStr_requestUrl() {
			return str_replace( $this->getStr_baseUrl(), '', $this->getStr_urlFull() );
		}

		/**
		 * Возвращает полный запрошенный URL
		 *
		 * @return string
		 */
		public function getStr_urlFull() {
			$https = ( !empty( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] !== 'off' ) || $_SERVER[ 'SERVER_PORT' ] == 443;

			return rtrim( 'http' . ( $https ? 's' : '' ) . '://' . $_SERVER[ 'HTTP_HOST' ], '/' ) . $_SERVER[ 'REQUEST_URI' ];
		}


		/**
		 * Отправляет почту одному или нескольким адресатам
		 *
		 * @version 1.8
		 *
		 * @param null   $to          - адрес ящика, либо 'mail1@mail.ru,mail2@mail.ru...', либо array(mail1@mail.ru, mail2@mail.ru, ...)
		 * @param string $theme       - тема сообщения
		 * @param string $htmlContent - содержимое письма (поддерживает формат HTML)
		 *
		 * @return bool
		 */
		function do_mail( $to = null, $theme, $htmlContent, $from = null )//, $file_patch=''  )
		{
			if( is_null( $to ) ) return false;
			if( is_string( $to ) && strpos( $to, ',' ) !== false ) {
				$to = explode( ',', $to );
			}
			if( !is_array( $to ) ) {
				$to = array( $to );
			}
			$firstMail = null;
			$alreadySendMail = array();
			foreach( $to as $mail ) {
				if( is_null( $firstMail ) ) $firstMail = $mail;
				if( in_array( $mail, $alreadySendMail ) ) continue;
				$alreadySendMail[] = $mail;
			}
			//$to =  array_shift($alreadySendMail);
			$from = hiweb()->string()->getStr_ifEmpty( $from, '"' . get_bloginfo( 'name' ) . '" <' . get_bloginfo( 'admin_email' ) . '>' );
			$r = true;
			///
			foreach( $alreadySendMail as $mail ) {
				$boundary = "--" . md5( uniqid( time() ) ); // генерируем разделитель
				$headers = "MIME-Version: 1.0\n";
				$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\n";
				//$headers .="To: ".$mail."\n";
				$headers .= "From: $from\n";
				$headers .= "Reply-To: $from\n";
				$headers .= "X-Mailer: PHP/" . phpversion() . "\n";
				$multipart = "--$boundary\n";
				$multipart .= "Content-Type: text/html; charset=utf-8\n";
				$multipart .= "Content-Transfer-Encoding: Quot-Printed\n\n";
				$multipart .= "$htmlContent\n\n";
				$message_part = '';
				$multipart .= $message_part . "--$boundary--\n";
				$r = $r && mail( $mail, $theme, $multipart, $headers ) ? true : false;
			}

			return $r;
		}


		/**
		 * Кэширует и возвращает данные
		 *
		 * @param $result
		 *
		 * @return mixed
		 */
		public function cache( $result = null ) {
			if( !$this->_cacheEnable ) return $result;
			///
			$dbArr = $this->getArr_debugBacktrace( 1, 1, 0, 0, 1, 1, 3, 3, '', '', 1 );
			$keyArr = array();
			if( !is_null( $this->array2()->getVal( $dbArr, array( 'path', 0 ) ) ) ) $keyArr[] = $this->array2()->getVal( $dbArr, array( 'path', 0 ) );
			if( !is_null( $this->array2()->getVal( $dbArr, array( 'className', 0 ) ) ) ) $keyArr[] = $this->array2()->getVal( $dbArr, array( 'className', 0 ) );
			if( !is_null( $this->array2()->getVal( $dbArr, array( 'functionName', 0 ) ) ) ) $keyArr[] = $this->array2()->getVal( $dbArr, array( 'functionName', 0 ) );
			if( !is_null( $this->array2()->getVal( $dbArr, array( 'args', 0 ) ) ) ) $keyArr[] = md5( json_encode( $this->array2()->getVal( $dbArr, array( 'args', 0 ) ) ) );
			$key = implode( ':', $keyArr );
			if( is_null( $this->array2()->getVal( $this->_cache, $key ) ) ) {
				$this->_cache[ $key ] = $result;

				return $result;
			} else {
				return $this->array2()->getVal( $this->_cache, $key );
			}
		}

		public function cacheExists() {
			if( !$this->_cacheEnable ) return false;
			//
			$dbArr = $this->getArr_debugBacktrace( 1, 1, 0, 0, 1, 1, 3, 3, '', '', 1 );
			$keyArr = array();
			if( !is_null( $this->array2()->getVal( $dbArr, array( 'path', 0 ) ) ) ) $keyArr[] = $this->array2()->getVal( $dbArr, array( 'path', 0 ) );
			if( !is_null( $this->array2()->getVal( $dbArr, array( 'className', 0 ) ) ) ) $keyArr[] = $this->array2()->getVal( $dbArr, array( 'className', 0 ) );
			if( !is_null( $this->array2()->getVal( $dbArr, array( 'functionName', 0 ) ) ) ) $keyArr[] = $this->array2()->getVal( $dbArr, array( 'functionName', 0 ) );
			if( !is_null( $this->array2()->getVal( $dbArr, array( 'args', 0 ) ) ) ) $keyArr[] = md5( json_encode( $this->array2()->getVal( $dbArr, array( 'args', 0 ) ) ) );
			$key = implode( ':', $keyArr );

			return !is_null( $this->array2()->getVal( $this->_cache, $key ) );
		}

		/**
		 * Подключить кэш-файл, возвращает путь до файла
		 */
		private function cacheToFileSetup() {
			if( !$this->_cacheByFileEnable ) return false;
			///
			$cacheFile = HIWEB_DIR_CACHE . DIR_SEPARATOR . $this->_cacheByFileName;
			if( is_null( $this->_cacheByFile ) ) {
				if( !file_exists( $cacheFile ) ) {
					$this->file()->do_foldersAutoCreate( dirname( $cacheFile ) );
					file_put_contents( $cacheFile, json_encode( array() ) );
				}
				$this->_cacheByFile = $this->file()->getMix_fromJSONFile( $cacheFile, array() );
			}

			return $cacheFile;
		}

		/**
		 * Сохранить текущие значения в файл
		 *
		 * @param null $key    - установить ключ значения (не обязат)
		 * @param null $result - установить значение (не обязат)
		 *
		 * @return bool
		 */
		private function cacheByFileSave( $key = null, $result = null ) {
			if( !$this->_cacheByFileEnable ) return false;
			///
			if( !is_null( $key ) && !is_null( $result ) ) $this->_cacheByFile[ $key ] = $result;
			if( !is_null( $this->_cacheByFile ) ) return file_put_contents( $this->cacheToFileSetup(), json_encode( $this->_cacheByFile ) );

			return true;
		}

		/**
		 * Удаляет файл кэша
		 *
		 * @return bool
		 */
		public function cacheByFileClear() {
			return hiweb()->file()->do_unlinkDir( HIWEB_DIR_CACHE );
			//$cacheFile = HIWEB_DIR_CACHE.DIR_SEPARATOR.$this->_cacheByFileName;
			//if(file_exists($cacheFile)) return @unlink($cacheFile);
			//return true;
		}

		/**
		 * Сохранить в файл / получить из файла значение кэша
		 *
		 * @param null $result - установить значение кэша
		 *
		 * @return mixed|null
		 */
		public function cacheByFile( $result = null ) {
			if( !$this->_cacheByFileEnable ) return $result;
			$this->cacheToFileSetup();
			///
			$dbArr = $this->getArr_debugBacktrace( 1, 1, 0, 0, 1, 1, 3, 3, '', '', 1 );
			$keyArr = array();
			if( !is_null( $this->array2()->getVal( $dbArr, array( 'path', 0 ) ) ) ) $keyArr[] = $this->array2()->getVal( $dbArr, array( 'path', 0 ) );
			if( !is_null( $this->array2()->getVal( $dbArr, array( 'className', 0 ) ) ) ) $keyArr[] = $this->array2()->getVal( $dbArr, array( 'className', 0 ) );
			if( !is_null( $this->array2()->getVal( $dbArr, array( 'functionName', 0 ) ) ) ) $keyArr[] = $this->array2()->getVal( $dbArr, array( 'functionName', 0 ) );
			if( !is_null( $this->array2()->getVal( $dbArr, array( 'args', 0 ) ) ) ) $keyArr[] = md5( json_encode( $this->array2()->getVal( $dbArr, array( 'args', 0 ) ) ) );
			$key = implode( ':', $keyArr );
			if( !is_null( $result ) ) {
				$this->cacheByFileSave( $key, $result );
			}
			if( is_null( $this->array2()->getVal( $this->_cacheByFile, $key ) ) ) {
				return $result;
			} else {
				return $this->array2()->getVal( $this->_cacheByFile, $key );
			}
		}

		/**
		 * Возвращает TRUE, если кэш существует
		 *
		 * @return bool
		 */
		public function cacheByFileExists() {
			if( !$this->_cacheByFileEnable ) return false;
			$this->cacheToFileSetup();
			///
			$dbArr = $this->getArr_debugBacktrace( 1, 1, 0, 0, 1, 1, 3, 3, '', '', 1 );
			$keyArr = array();
			if( !is_null( $this->array2()->getVal( $dbArr, array( 'path', 0 ) ) ) ) $keyArr[] = $this->array2()->getVal( $dbArr, array( 'path', 0 ) );
			if( !is_null( $this->array2()->getVal( $dbArr, array( 'className', 0 ) ) ) ) $keyArr[] = $this->array2()->getVal( $dbArr, array( 'className', 0 ) );
			if( !is_null( $this->array2()->getVal( $dbArr, array( 'functionName', 0 ) ) ) ) $keyArr[] = $this->array2()->getVal( $dbArr, array( 'functionName', 0 ) );
			if( !is_null( $this->array2()->getVal( $dbArr, array( 'args', 0 ) ) ) ) $keyArr[] = md5( json_encode( $this->array2()->getVal( $dbArr, array( 'args', 0 ) ) ) );
			$key = implode( ':', $keyArr );

			return !is_null( $this->array2()->getVal( $this->_cacheByFile, $key ) );
		}


	}