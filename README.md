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
Basic CommandLine-Interface implemeted

![01-CommandLineInterface](https://user-images.githubusercontent.com/48651533/116916627-e044da00-ac4d-11eb-826d-925145fb02d7.png)


Outdated LanguageFile before using my script

![02-Outdated-LanguageFile](https://user-images.githubusercontent.com/48651533/116916867-29952980-ac4e-11eb-8ec4-a09f7eccf3ce.png)


Updated Language-File after using my script ... exsiting translations are still intact, new language-keys have been added

![03-Updated-LanguageFile](https://user-images.githubusercontent.com/48651533/116916975-50ebf680-ac4e-11eb-8553-bd721f710560.png)


Used the option `$prepare_all_other_langs = TRUE;` ... translation-files have been "prepared" to be translated by others

![04-PreparedForeign-LanguageFile](https://user-images.githubusercontent.com/48651533/116917272-b9d36e80-ac4e-11eb-9e7a-6be16b819e60.png)


Imagine someone else has made PRs to your plugin to translate one of the "prepared" languages _(that are foreign to you)_

![05-TranslatedForeign-LanguageFile](https://user-images.githubusercontent.com/48651533/116917421-ef785780-ac4e-11eb-9155-ca8f5a5c6c48.png)


After further development of your plugin, you rerun the script and NEW language-keys are again "prepared" into the "foreign" language-files, while still preserving the existing translations

![06-UpdatedTranslatedForeign-LanguageFile](https://user-images.githubusercontent.com/48651533/116917622-2e0e1200-ac4f-11eb-94a1-787a58c022b0.png)



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
```php
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
