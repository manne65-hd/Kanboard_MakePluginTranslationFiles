<?php
/*******************************************************************************
**      Make (Kanboard)Plugin-Translation-Files
** =================================================
** A(self-contained) CLI-tool to find all calls to Kanboard's translate-function
** in your plugin and generate/update translation-files for it.
** -----------------------------------------------------------------------------
** @Author: Manfred Hoffmann
** @Version: 0.1.0 (2021-05-03)
** -----------------------------------------------------------------------------
********************************************************************************/

/*==============================================================================
 *                          START of configuration
 *                         ------------------------
 * BELOW you'll find some parameters you'll have to adjust,
 * in order to configure this script.
 *============================================================================*/
// foldername of the plugin for which you want to prepare/update translations
$my_plugin_folder = 'My_TestPlugin'; // CASE-sensitive!

/* array of language-codes for which you want to offer translations
 * MUST be a vaild code as available in Kanboard/app/Model/LanguageModel.php */
$my_plugin_langs = array(
     'xy_XY',
     'zz_ZZ',
);
$my_plugin_langs = array(
    'de_DE',
    'de_DE_du',
);

/* set to TRUE if you want to prepare translations for all other languages.
 * This will generate language-files with all statements commented out like:
 *    // 'Your first term' => '',
 *    // 'another term' => '',
 */
$prepare_all_other_langs = FALSE;

// set to TRUE if you want to generate a LOG-file of the process
$log_to_file = FALSE; // not implemeted yet!!

/*==============================================================================
 *                           END of configuration
 *                          ----------------------
 *          !!!!! DON'T MAKE ANY CHANGES BELOW THIS LINE !!!!!
 *============================================================================*/















/* let's try to use the LanguageModel from Kanboard later(if that is possible)

use Kanboard\Model\LanguageModel;
require_once __DIR__.'/../app/Core/Base.php';
require_once __DIR__.'/../app/MOdel/LanguageModel.php';
$kb_lang_model = new languageModel;
*/

// DIE-message when called via browser
define('NON_CLI_DIE_MESSAGE', 'This script can only be run in CommandLineMode!');

// make sure the script only runs when called via CLI otherwise > DIE!
(PHP_SAPI !== 'cli' || isset($_SERVER['HTTP_USER_AGENT'])) && die(NON_CLI_DIE_MESSAGE);

/* -----------------------------------------------------------------------------
 * Let's start ...
 *      - setup required variables
 *      - check user-parameters for validity
 *-----------------------------------------------------------------------------*/
$mpt_config = array();
$my_plugin_folder        = (isset($my_plugin_folder)) ? $my_plugin_folder : '';
$my_plugin_langs         = (isset($my_plugin_langs)) ? $my_plugin_langs : FALSE;
$prepare_all_other_langs = (isset($prepare_all_other_langs)) ? $prepare_all_other_langs : FALSE;
$log_to_file             = (isset($log_to_file)) ? $log_to_file : FALSE;
initialize($my_plugin_folder, $my_plugin_langs, $prepare_all_other_langs, $log_to_file);

// check if $my_plugin_folder is an(existing) folder
if ($my_plugin_folder === 'My_KanboardPlugin') {
    echoMessage('You must configure the script by setting the variable $my_plugin_folder!', 'w');
    die;
} else {
    if (file_exists($mpt_config['translate_plugin'])) {
        if (!is_dir($mpt_config['translate_plugin'])) {
                mpt_die('Not a folder!');
            }
    } else {
        mpt_die('Folder not found!');
    }
}

// check if $my_plugin_langs contain(ONLY) valid language-codes
checkLangsValid();

/* -----------------------------------------------------------------------------
 * Find and extract all language keys ...
 *      - get all language keys from Kanboard
 *      - get a list of all PHP-files in the plugin's directory-tree
 *      - find and extract UNIQUE language-keys for all scripts
 *      - MERGE them into one UNIQUE array without multiple occurences
 *      - and finally REMOVE all Kanboard-core-lang-keys!
 *-----------------------------------------------------------------------------*/
// get all language keys from Kanboard's "master-language"(french translation)
$kb_lang_keys = getLangKeys($mpt_config['kb_master_lang']);

// get a list of all PHP-files in the plugin's directory-tree
$plugin_scripts = getPluginScripts($mpt_config['translate_plugin']);

// find and extract UNIQUE language-keys for all scripts
$script_lang_keys = array();
foreach ($plugin_scripts as $plugin_script) {
    //$script_lang_keys[] = getLangTerms($plugin_script);
    $script_lang_keys[] = getScriptKeys($plugin_script);
}

// MERGE all $script_lang_keys into $plugin_all_lang_keys
$plugin_all_lang_keys = array();
foreach ($script_lang_keys as $merge_keys) {
    if (count($plugin_all_lang_keys) === 0 ) {
        if ($merge_keys['lang_keys']) {
            $plugin_all_lang_keys = $merge_keys['lang_keys'];
        }
    } else {
        if ($merge_keys['lang_keys']) {
            $plugin_all_lang_keys = array_merge($plugin_all_lang_keys, $merge_keys['lang_keys']);
        }
    }
}

// REMOVE multiple occurences of the same lang_keys
$plugin_unique_lang_keys = array_unique($plugin_all_lang_keys);

// and finally REMOVE all Kanboard-language-keys, thus returning only
// language-keys specific for this plugin, that actually need translation!
$translate_plugin_lang_keys = array_diff($plugin_unique_lang_keys, $kb_lang_keys);

/* -----------------------------------------------------------------------------
 * Let's start generating translation-files ...
 *      - ITERATE $mpt_config['my_plugin_langs']
 *          - get (existing) translations for the current language
 *          - write new/updated translations.php
 *            (preserving existing translations and adding new ones!)
 *      - DONE ITERATING $mpt_config['my_plugin_langs']
 *      - IF $prepare_all_other_langs = TRUE
 *          - ITERATE over all other languages
 *              - get (existing) translations for the current language
 *              - write new/updated translations.php
 *                NEW language-keys will be commented out!
 *                ... but existing translations are preserved and stay active
 *          - DONE ITERATING all other languages
 *-----------------------------------------------------------------------------*/


foreach ($mpt_config['my_plugin_langs'] as $translate_lang) {
    $translation_file = $mpt_config['translate_plugin'] . '\Locale\\' . $translate_lang . '\translations.php';
    $translation_keys = getTranslations($translation_file);

    // we might have to create the folder first ...
    $translation_folder = dirname($translation_file);
    if (!file_exists($translation_folder)) {
        mkdir($translation_folder);
    }

    makeTranslation($translation_file, $translate_plugin_lang_keys, $translation_keys);
}

if ($mpt_config['prep_other_langs']) {
    $prepare_other_langs = otherLangs();
    foreach ($prepare_other_langs as $translate_lang => $lang_name) {
        $translation_file = $mpt_config['translate_plugin'] . '\Locale\\' . $translate_lang . '\translations.php';
        $translation_keys = getTranslations($translation_file);

        // we might have to create the folder first ...
        $translation_folder = dirname($translation_file);
        if (!file_exists($translation_folder)) {
            mkdir($translation_folder);
        }

        makeTranslation($translation_file, $translate_plugin_lang_keys, $translation_keys, PREPARE_TRANSLATION);
    }
}

echoMessage('Succesfully generated translation-files', 's');
// done and dusted for NOW ... let's go BETA

/*******************************************************************************
********************************************************************************
*****                                                                      *****
*****      All required functions included in a self-contained script      *****
*****     ============================================================     *****
*****                                                                      *****
********************************************************************************
*******************************************************************************/

/**
 * Return an array of all PHP-scripts used in the current plugin
 *
 * @return array
 */
function initialize($my_plugin_folder, $my_plugin_langs, $prepare_all_other_langs, $log_to_file) {
    global $mpt_config;
    global $kb_lang_model;
    define('PREPARE_TRANSLATION', TRUE);

    // assign config-variables assigned by user to global config-array
    $mpt_config['my_plugin_folder'] = $my_plugin_folder;
    $mpt_config['my_plugin_langs']  = $my_plugin_langs;
    $mpt_config['prep_other_langs'] = $prepare_all_other_langs;
    $mpt_config['log_to_file']      = $log_to_file;

    // setup basic paths & files
    $mpt_config['path_kb_root']     = dirname(__DIR__);
    $mpt_config['path_plugins']     = $mpt_config['path_kb_root'] . '\plugins';
    $mpt_config['path_locales']     = $mpt_config['path_kb_root'] . '\app\Locale';
    $mpt_config['kb_master_lang']   = $mpt_config['path_kb_root'] . '\app\Locale\fr_FR\translations.php';
    $mpt_config['translate_plugin'] = $mpt_config['path_plugins'] . '\\' . $mpt_config['my_plugin_folder'];

    // get all available languages in Kanboard
    //$mpt_config['kb_all_langs'] = $kb_lang_model->getLanguages(); // don't know if this can even be done
    $mpt_config['kb_all_langs'] = getLanguages();

}
/**
 * Return an array of all PHP-scripts used in the current plugin
 *
 * @return array
 */
function getPluginScripts($plugin_folder) {
    // files and folders to ignore
    $ignore_pattern = array(
        '.git',
        'assets',
        'Locale',
        'Plugin.php',
    );

    // manually add the Plugin.php-script as the first script ...
    // this enables us, to add translations from that script in the first place!
    $plugin_scripts = array($plugin_folder . '\Plugin.php');

    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($plugin_folder));
    foreach ($rii as $file)
        if (!$file->isDir() && $file->getExtension() === 'php') {
            if (!haystackHasNeedle($file->getPathname(), $ignore_pattern)) {
                $plugin_scripts[] = $file->getPathname();
            }
        }

    return $plugin_scripts;
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
            mpt_die('Error while reading ' . $lang_file);
        }
        fclose($handle);
    }
    return $lang_keys;
}

/**
 * Return an array of the existing translations of a given language
 *
 * @param string $lang_file Script to search for translations
 *
 * @return array Associative array with [$lang_key] => [$translation]
 */
function getTranslations($lang_file) {
    $translated_keys = array();

    $handle = @fopen($lang_file, "r");
    if ($handle) {
        while (($buffer = fgets($handle, 4096)) !== false) {
            $extract = explode('=>', $buffer);
            $key_found = FALSE;
            if (substr(trim($extract[0]), 0, 1) === "'") {
                // strip off whitespaces
                $lang_key = trim($extract[0]);
                // strip off leading '
                $lang_key = substr($lang_key, 1);
                // strip off trailing '
                $lang_key = substr($lang_key, 0, -1);

                // let's extract the translation
                if (substr(trim($extract[1]), 0, 1) === "'") {
                    // strip off whitespaces
                    $translation = trim($extract[1]);
                    // strip off leading '
                    $translation = substr($translation, 1);
                    // strip off trailing '
                    $translation = substr($translation, 0, -2);
                }

                $key_found = TRUE;
            }
            // add lang_key and translation to the list
            if ($key_found) {
                $translated_keys[$lang_key] = $translation;
            }
        }
        if (!feof($handle)) {
            mpt_die('Error while reading ' . $lang_file);
        }
        fclose($handle);
    }
    return $translated_keys;
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
    $regx_find_lang_keys = '/(?<= t\(\')(.*?[^\\\\])(?=\')/m';  // Thanks to DrDeath :-D ( https://github.com/DrDeath )

    $handle = @fopen($script_file, "r");
    if ($handle) {
        while (($buffer = fgets($handle, 4096)) !== false) {
            preg_match_all($regx_find_lang_keys, $buffer, $matches, PREG_SET_ORDER, 0);
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
            mpt_die('Error while reading ' . $script_file);
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
function makeTranslation($lang_file, $trans_keys, $translated_keys = array('foo' => 'bar'), $prepare_translation = FALSE) {
    // try opening file in WRITE-mode
    if (!$handle = fopen($lang_file, 'w')) {
        mpt_die('Error while trying to create/update ' . $lang_file);
    };


    // generate PHP opening-tags and required code for the array ...
    if (!fwrite($handle, getTransHeader())) {
        mpt_die('Error while trying to write to ' . $lang_file);
    }
        // now let's iterate over the keys to get translated and generate the code
        foreach ($trans_keys as $trans_key) {
            // Check if current lang_key has already been translated
            if (array_key_exists($trans_key, $translated_keys)) {
                $trans_line = "    '$trans_key' => '$translated_keys[$trans_key]'," . PHP_EOL;
            } else {
                $trans_line  = ($prepare_translation) ? "    // " : "    ";
                $trans_line .= "'$trans_key' => ''," . PHP_EOL;
            }
            if (!fwrite($handle, $trans_line)) {
                mpt_die('Error while trying to write to ' . $lang_file);
            }
        }

    // generate final code and colse the file-handle
    if (!fwrite($handle, getTransFooter())) {
        mpt_die('Error while trying to close ' . $lang_file);
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
 * Check if all languages given by $my_plugin_langs are valid
 * ... otherwise DIE with an ERROR-message
 *
 */
function checkLangsValid() {
    global $mpt_config;

    $example_plugin_langs = array(
        'xy_XY',
        'zz_ZZ',
    );
    if (! array_diff($mpt_config['my_plugin_langs'], $example_plugin_langs)) {
        echoMessage('You must configure the script by setting the array $my_plugin_langs!', 'w');
        die;
    }

    if(! is_array($mpt_config['my_plugin_langs'])) {
        mpt_die('$my_plugin_langs MUST be an array!');
    }
    foreach($mpt_config['my_plugin_langs'] as $my_plugin_lang) {
        if(! array_key_exists($my_plugin_lang, $mpt_config['kb_all_langs'])) {
            mpt_die($my_plugin_lang . ' is not a valid language-code!');
        }
    }
}

/**
 * Return all language-codes EXCEPT for those configured in $my_plugin_langs
 *
 * @return array
 */
function otherLangs() {
    global $mpt_config;
    $other_langs = array();

    foreach($mpt_config['kb_all_langs'] as $lang_key => $lang_name) {
        if(! in_array($lang_key, $mpt_config['my_plugin_langs'])) {
            $other_langs[$lang_key] = $lang_name;
        }
    }
    return $other_langs;
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
 * DIE with ERROR-message
 *
 * @param string $message The ERROR-message to be displayed
 *
 */
function mpt_die($error){
    echoMessage($error, 'e');
    die;
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
