<?php
/*******************************************************************************
**      Make (Kanboard)Plugin-Translation-Files
** =================================================
** A(self-contained) CLI-tool to find all calls to Kanboard's translate-function
** in your plugin and generate/update translation-files for it.
** -----------------------------------------------------------------------------
** @Author: Manfred Hoffmann
** @Version: 0.0.1 (2021-05-02)
** -----------------------------------------------------------------------------
********************************************************************************/

/*==============================================================================
 *                          START of configuration
 *                         ------------------------
 * BELOW you'll find some parameters you'll have to adjust,
 * in order to configure this script.
 *============================================================================*/
// foldername of the plugin for which you want to prepare/update translations
$my_plugin_folder = 'My_KanboardPlugin'; // CASE-sensitive!

/* array of language-codes for which you want to offer translations
 * MUST be a vaild code as available in Kanboard/app/Model/LanguageModel.php */
$my_plugin_langs = array(
    'xy_XY',
    'zz_ZZ',
);

/* set to TRUE if you want to prepare translations for all other languages
 * this will generate language-files with all statements commented out like:
 *    // 'Your first term' => '',
 *    // 'another term' => '',
 */
$prepare_all_other_langs = TRUE;

// set to TRUE if you want to generate a LOG-file of the process
$log_to_file = TRUE;

/*==============================================================================
 *                           END of configuration
 *                          ----------------------
 *          !!!!! DON'T MAKE ANY CHANGES BELOW THIS LINE !!!!!
 *============================================================================*/















 define('NON_CLI_DIE_MESSAGE', 'This script can only be run in CommandLineMode!');

// make sure the script only runs when called via CLI otherwise > DIE!
//(PHP_SAPI !== 'cli' || isset($_SERVER['HTTP_USER_AGENT'])) && die(NON_CLI_DIE_MESSAGE);

// Let's start ...
$mpt_config = array();
$my_plugin_folder        = (isset($my_plugin_folder)) ? $my_plugin_folder : '';
$my_plugin_langs         = (isset($my_plugin_langs)) ? $my_plugin_langs : array();
$prepare_all_other_langs = (isset($prepare_all_other_langs)) ? $prepare_all_other_langs : FALSE;
$log_to_file             = (isset($log_to_file)) ? $log_to_file : FALSE;
initialize($my_plugin_folder, $my_plugin_langs, $prepare_all_other_langs, $log_to_file);

ddd($mpt_config);
//echoMessage('KB_make_plugin_translations Version 0.0.1 by manne65hd', 's');

// get all language keys from kanboard(french translation)
$kb_lang_keys = getLangKeys($mpt_config['kb_lang']);

// get a list of all PHP-files in the plugin's directory-tree
$plugin_scripts = getPluginScripts();


// find and extract UNIQUE language-keys for all scripts
$script_lang_keys = array();
foreach ($plugin_scripts as $plugin_script) {
    //$script_lang_keys[] = getLangTerms($plugin_script);
    $script_lang_keys[] = getScriptKeys($plugin_script);
}
//dd($script_lang_keys);

// MERGE all $script_lang_keys into $all_plugin_lang_keys
$all_plugin_lang_keys = array();
foreach ($script_lang_keys as $merge_keys) {
    if (count($all_plugin_lang_keys) === 0 ) {
        if ($merge_keys['lang_keys']) {
            $all_plugin_lang_keys = $merge_keys['lang_keys'];
        }
    } else {
        if ($merge_keys['lang_keys']) {
            $all_plugin_lang_keys = array_merge($all_plugin_lang_keys, $merge_keys['lang_keys']);
        }
    }
}
//dd($all_plugin_lang_keys);

// make unique ...
$unique_plugin_lang_keys = array_unique($all_plugin_lang_keys);
//dd($unique_plugin_lang_keys);

// and finally remove all Kanboard-language-keys, thus returning only
// language-keys specific for this plugin, that actually need translation!
$translate_plugin_lang_keys = array_diff($unique_plugin_lang_keys, $kb_lang_keys);
//ddd($translate_plugin_lang_keys);


makeTranslation($translate_plugin_lang_keys);




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
function initialize($my_plugin_folder, $my_plugin_langs, $prepare_all_other_langs, $log_to_file) {
    global $mpt_config;

    // assign config-variables assigned by user to global config-array
    $mpt_config['my_plugin_folder'] = $my_plugin_folder;
    $mpt_config['my_plugin_langs'] = $my_plugin_langs;
    $mpt_config['prep_other_langs'] = $prepare_all_other_langs;
    $mpt_config['log_to_file'] = $log_to_file;

    // setup basic paths & files
    $mpt_config['path_plugins'] = dirname(__DIR__);
    $mpt_config['path_kb_root'] = dirname($mpt_config['path_plugins']);
    $mpt_config['path_locales'] = $mpt_config['path_kb_root'] . '\app\Locale';
    $mpt_config['kb_lang'] = $mpt_config['path_kb_root'] . '\app\Locale\fr_FR\translations.php';

}
/**
 * Return an array of all PHP-scripts used in the current plugin
 *
 * @return array
 */
function getPluginScripts() {
    // files and folders to ignore
    $ignore_pattern = array(
        'KB_make_plugin_translations.php',
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
 * Return the array of the Kanboard's core language-keys
 *
 * @param string $lang_file Script to search for Kanboard's language-keys
 *
 * @return array
 */
function getLangKeys($lang_file) {
    $lang_keys = array();

    $handle = @fopen($lang_file, "r");
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
                $lang_keys[] = $lang_key;
            }
        }
        if (!feof($handle)) {
            echo "Fehler: unerwarteter fgets() Fehlschlag\n";
        }
        fclose($handle);
    }
    return $lang_keys;
}

/**
 * Return an array of language-keys used in the given script
 *
 * @param string $script_file Script to search for calls to the translate-function t('foo')
 *
 * @return array
 */
function getScriptKeys($script_file) {
    $lang_keys = array();
    $lang_keys['script_file'] = $script_file;
    $all_lang_keys['lang_keys'] = array();
    // REGEXpression to find language-keys
    $regx_find_langterm = '/(?<=t\(\')(.*?)(?=\'\))/m';
    $regx_find_langterm = '/(?<= t\(\')(.*?)(?=\')/m';

    $handle = @fopen($script_file, "r");
    if ($handle) {
        while (($buffer = fgets($handle, 4096)) !== false) {
            preg_match_all($regx_find_langterm, $buffer, $matches, PREG_SET_ORDER, 0);
            if ( count($matches) ) {
                //dd($matches[0][0]);
                $all_lang_keys['lang_keys'][] = $matches[0][0];
            }
        }
        // IF no matches where found --> convert $all_lang_keys['lang_keys'] from empty array to FALSE
        $all_lang_keys['lang_keys'] =  (! count($all_lang_keys['lang_keys'])) ? FALSE : $all_lang_keys['lang_keys'];
        // make UNIQUE
        if (! $all_lang_keys['lang_keys']) {
            $lang_keys['lang_keys'] = FALSE;
            // add some more information
            $lang_keys['num_keys_found'] = FALSE;
            $lang_keys['num_keys_unique'] = FALSE;
        } else {
            $lang_keys['lang_keys'] = array_unique($all_lang_keys['lang_keys']);
            // add some more information
            $lang_keys['num_keys_found'] = count($all_lang_keys['lang_keys']);
            $lang_keys['num_keys_unique'] = count($lang_keys['lang_keys']);
        }


        if (!feof($handle)) {
            echo "Fehler: unerwarteter fgets() Fehlschlag\n";
        }
        fclose($handle);
    }
    return $lang_keys;
}

/**
 * Make (generate or update) a translation-file
 *
 * @param array $trans_keys Language-Keys that need translation
 *
 * @return bool TRUE if successful or else > FALSE
 */
function makeTranslation($trans_keys) {
    // try opening file in WRITE-mode
    if (!$handle = fopen('translations.php', 'w')) {
        die('ERROR');
    };


    // generate PHP opening-tags and required code for the array ...
    if (!fwrite($handle, getTransHeader())) {
        die('ERROR');
    }
        // now let's iterate over the keys to get translated and generate the code
        foreach ($trans_keys as $trans_key) {
            //$key_line = "    '$trans_key' => ''" . PHP_EOL;
            if (!fwrite($handle, "    '$trans_key' => ''," . PHP_EOL)) {
                die('ERROR');
            }
        }

    // generate final code and colse the file-handle
    if (!fwrite($handle, getTransFooter())) {
        die('ERROR');
    }
    fclose($handle);

}

/**
 * Get available languages // copied from Kanboard/LanguageModel.php
 *
 * @return array Associative Array with language-codes and corresponding names.
 */
function getLanguages() {
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
 * return the HEADER of a tranlations-file
 *
 * @return string
 */
function getTransHeader(){
$file_header = <<<EOD
<?
// Plugin-translation-file generated with **KB_make_plugin_translations.php**
// Check it out at https://github.com/manne65-hd/Kanboard_MakePluginTranslationFiles
return array(

EOD;

    return $file_header;
}

/**
 * return the FOOTER of a tranlations-file
 *
 * @return string
 */
function getTransFooter(){
$file_footer = <<<EOD
);

EOD;

    return $file_footer;
}


/**
 * ECHO a (colored) message to the screen
 *
 * @param string $message The message to be displayed
 * @param string $type (i)nfo = BLUE, (w)arning = YELLOW, (e)rror = RED or (s)uccess = GREEN
 *
 */
function echoMessage($message, $type = ''){
    switch ($type) {
        case 'e': // error RED
            echo "\033[31m$message \033[0m\n";
        break;
        case 's': // success GREEN
            echo "\033[32m$message \033[0m\n";
        break;
        case 'w': // warning YELLOW
            echo "\033[33m$message \033[0m\n";
        break;
        case 'i': // info BLUE
            echo "\033[36m$message \033[0m\n";
        break;
        default: // white
            echo "$message\n";
        break;
    }
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
    echo '</pre><hr>' . PHP_EOL;
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
