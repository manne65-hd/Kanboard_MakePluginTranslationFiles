<?php
/*******************************************************************************
**      Make (Kanboard)Plugin-Translation-Files
** =================================================
**
** A(self-contained) CLI-tool to find all calls to Kanboard's translate-function
** in your plugin and generate/update translation-files for it.
** -----------------------------------------------------------------------------
** @Author: Manfred Hoffmann
** @Version: 0.0.1 (2021-05-02)
**
*******************************************************************************/

// Leave blank if users should get a BLANK page, when calling the script
// via browser ... or define an error-message of your choice!
define('NON_CLI_DIE_MESSAGE', 'This script can only be run in CommandLineMode!');

$mpl_config = array();

// make sure the script only runs when called via CLI
//(PHP_SAPI !== 'cli' || isset($_SERVER['HTTP_USER_AGENT'])) && die(NON_CLI_DIE_MESSAGE);

initialize();
// get all language keys from kanboard (french translation)
$master_lang_keys = getMasterLangKeys($mpl_config['master_lang']);


//dd($master_lang_keys);

$script_lang_keys = array();
$plugin_scripts = getPluginScripts();
foreach ($plugin_scripts as $plugin_script) {
    //$script_lang_keys[] = getLangTerms($plugin_script);
    $script_lang_keys[] = getLangTerms($plugin_script);
}
//ddd($script_lang_keys);

$plugin_lang_keys = array();
foreach ($script_lang_keys as $merge_keys) {
    if (count($plugin_lang_keys) === 0 ) {
        if (is_array($merge_keys)) {
            $plugin_lang_keys = $merge_keys;
        }
    } else {
        if (is_array($merge_keys)) {
            $plugin_lang_keys = array_merge($plugin_lang_keys, $merge_keys);
        }
    }
}

dd($plugin_lang_keys);
exit;






/*******************************************************************************
**                                                                            **
**         All required functions included in a self-contained script         **
**        ============================================================        **
**                                                                            **
*******************************************************************************/

/**
 * Return an array of all PHP-scripts used in the current plugin
 *
 * @return array
 */
function initialize() {
    global $mpl_config;

    $mpl_config['path_plugins'] = dirname(__DIR__);
    $mpl_config['path_kb_root'] = dirname($mpl_config['path_plugins']);
    $mpl_config['master_lang'] = $mpl_config['path_kb_root'] . '\app\Locale\fr_FR\translations.php';

}
/**
 * Return an array of all PHP-scripts used in the current plugin
 *
 * @return array
 */
function getPluginScripts() {
    // files and folders to ignore
    $ignore_pattern = array(
        'KB_make_plugin_lang.php',
        '.git',
        'assets',
        'Locale',
        'Plugin.php',
    );

    // manually add the Plugin.php-script as the first script ...
    // this enables us, to add translations from that script in the first place!
    $plugin_scripts = array(__DIR__ . '\Plugin.php');

    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
    foreach ($rii as $file)
        if (!$file->isDir() && $file->getExtension() === 'php') {
            if (!haystackHasNeedle($file->getPathname(), $ignore_pattern)) {
                $plugin_scripts[] = $file->getPathname();
            }
        }

    return $plugin_scripts;
}

/**
 * Check if HAYSTACK contains at least one NEEDLE from an array of NEEDLES
 *
 * @param string $haystack HAYSTACK to search in
 * @param array $needles Needles to search for
 * @param int $offset (optional) position to start the search within haystack
 *
 * @return bool
 */
function haystackHasNeedle($haystack, $needles, $offset=0) {
    if(!is_array($needles)) $needles = array($needles);
    foreach($needles as $needle) {
        if(strpos($haystack, $needle, $offset) !== false) return true; // stop on first true result
    }
    return false;
}

/**
 * Return the array of the Kanboard's core language-terms
 *
 * @param string $script Script to search for calls to the translate-function t('foo')
 *
 * @return array
 */
function getMasterLangKeys($master_lang) {
    $master_lang_keys = array();

    $handle = @fopen($master_lang, "r");
    if ($handle) {
        while (($buffer = fgets($handle, 4096)) !== false) {
            $extract = explode('=>', $buffer);
            if (substr(trim($extract[0]), 0, 1) === "'") {
                // strip off whitespaces
                $lang_key = trim($extract[0]);
                // strip off leading '
                $lang_key = substr($lang_key, 1);
                // strip off trailing '
                $lang_key = substr($lang_key, 0, -1);
                // and add it to the list
                $master_lang_keys[] = $lang_key;
            }
        }
        if (!feof($handle)) {
            echo "Fehler: unerwarteter fgets() Fehlschlag\n";
        }
        fclose($handle);
    }
    return $master_lang_keys;
}

/**
 * Return an array of language-terms used in the given script
 *
 * @param string $script Script to search for calls to the translate-function t('foo')
 *
 * @return array
 */
function getLangTerms($script) {
    $lang_terms = array();
    // REGEXpression to find language-terms
    $regx_find_langterm = '/(?<=t\(\')(.*?)(?=\'\))/m';
    $regx_find_langterm = '/(?<=t\(\')(.*?)(?=\')/m';

    $handle = @fopen($script, "r");
    if ($handle) {
        while (($buffer = fgets($handle, 4096)) !== false) {
            preg_match_all($regx_find_langterm, $buffer, $matches, PREG_SET_ORDER, 0);
            if ( count($matches) ) {
                //dd($matches[0][0]);
                $lang_terms[] = $matches[0][0];
            };
        }
        if (!feof($handle)) {
            echo "Fehler: unerwarteter fgets() Fehlschlag\n";
        }
        fclose($handle);
    }
    return $lang_terms;
}

/**
 * Get available languages // copied from Kanboard/LanguageModel.php
 *
 * @return array Associative Array with language-codes and corresponding names.
 */
function getLanguages()
{
    // Sorted by value
    $languages = array(
        'id_ID' => 'Bahasa Indonesia',
        'bs_BA' => 'Bosanski',
        'ca_ES' => 'Català',
        'cs_CZ' => 'Čeština',
        'da_DK' => 'Dansk',
        'de_DE' => 'Deutsch (Sie)',
        'de_DE_du' => 'Deutsch (du)',
        'en_GB' => 'English (GB)',
        'en_US' => 'English (US)',
        'es_ES' => 'Español (España)',
        'es_VE' => 'Español (Venezuela)',
        'fr_FR' => 'Français',
        'el_GR' => 'Grec',
        'hr_HR' => 'Hrvatski',
        'it_IT' => 'Italiano',
        'hu_HU' => 'Magyar',
        'mk_MK' => 'Македонски',
        'my_MY' => 'Melayu',
        'nl_NL' => 'Nederlands',
        'nb_NO' => 'Norsk',
        'pl_PL' => 'Polski',
        'pt_PT' => 'Português',
        'pt_BR' => 'Português (Brasil)',
        'ro_RO' => 'Română',
        'ru_RU' => 'Русский',
        'sr_Latn_RS' => 'Srpski',
        'fi_FI' => 'Suomi',
        'sk_SK' => 'Slovenčina',
        'sv_SE' => 'Svenska',
        'tr_TR' => 'Türkçe',
        'uk_UA' => 'Українська',
        'ko_KR' => '한국어',
        'zh_CN' => '中文(简体)',
        'zh_TW' => '中文(繁體)',
        'ja_JP' => '日本語',
        'th_TH' => 'ไทย',
        'vi_VN' => 'Tiếng Việt',
        'fa_IR' => 'فارسی',
    );

    return $languages;
}

/**
 * PRINT DEBUG-Information
 *
 * @param mixed
 *
 */
function dd($debug_var) {
    echo '<pre>' . PHP_EOL;
    var_dump($debug_var);
    echo '</pre>' . PHP_EOL;
}

/**
 * PRINT DEBUG-Information and die
 *
 * @param mixed
 *
 */
function ddd($debug_var) {
    echo '<pre>' . PHP_EOL;
    var_dump($debug_var);
    echo '</pre>' . PHP_EOL;
    exit;
}
?>
