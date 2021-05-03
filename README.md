Make (Kanboard)Plugin-Translation-Files
=======================================
A _(self-contained)_ CLI-tool to find all calls to Kanboard's translate-function _t('Your language term')_ in your plugin and generate/update translation-files for it.

## THIS IS A BETA-release ... use at your own risk!!!
**Make backups of your plugins-folder before you use this!**

List of features
----------------
- Define translations that you want to provide in order to generate the required folders and translations-files in _app\Locale\_ prepared with all terms that need translation
- Optionally generate translation-files for ALL other languages, that have all assignments commented out, so they are already prepared for contributions by other users


Screenshots
-----------

No screenshots yet

ToDo ...
--------
- Introduce better ERROR-handling
- Add success CLI-messages with some statistics about the actions executed
- Add option to log even more verbose details into a log-file
- do lots more of testing

Author
------
- Manfred Hoffmann
- License MIT

Requirements
------------
- Any environment, where you can develop your Kanboard-plugins should be fine

Installation & Usage
------------
- Manual installation
  1. Create a new folder **_KB_make_plugin_translations** inside the root of your kanboard-development-Installation
  2. Download and unzip all files into that folder
- Clone from github
  1. Create a new folder **_KB_make_plugin_translations** inside the root of your kanboard-development-Installation
  2. Clone the repo into that folder

- Set some parameters by editing the script
```
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

    /* set to TRUE if you want to prepare translations for all other languages.
     * This will generate language-files with all statements commented out like:
     *    // 'Your first term' => '',
     *    // 'another term' => '',
     */
    $prepare_all_other_langs = FALSE;

    // set to TRUE if you want to generate a LOG-file of the process
    $log_to_file = FALSE;

    /*==============================================================================
     *                           END of configuration
     *                          ----------------------
     *          !!!!! DON'T MAKE ANY CHANGES BELOW THIS LINE !!!!!
     *============================================================================*/
```

- Call it from the command-line ... That's it ;-)
