<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ('abc' is just an example).
// Uncomment and edit this line to override:
# $plugin['name'] = 'abc_plugin';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 0;

$plugin['version'] = '0.1.3';
$plugin['author'] = 'Oleg Loukianov';
$plugin['author_uri'] = 'http://www.iut-fbleau.fr/projet/etc/';
$plugin['description'] = 'Work with flat file theme templates in debugging mode';

// Plugin load order:
// The default value of 5 would fit most plugins, while for instance comment
// spam evaluators or URL redirectors would probably want to run earlier
// (1...4) to prepare the environment for everything else that follows.
// Values 6...9 should be considered for plugins which would work late.
// This order is user-overrideable.
$plugin['order'] = '5';

// Plugin 'type' defines where the plugin is loaded
// 0 = public       : only on the public side of the website (default)
// 1 = public+admin : on both the public and non-AJAX admin side
// 2 = library      : only when include_plugin() or require_plugin() is called
// 3 = admin        : only on the non-AJAX admin side
// 4 = admin+ajax   : only on admin side
// 5 = public+admin+ajax   : on both the public and admin side
$plugin['type'] = '5';

// Plugin 'flags' signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use.
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

$plugin['flags'] = '0';

// Plugin 'textpack' is optional. It provides i18n strings to be used in conjunction with gTxt().
// Syntax:
// ## arbitrary comment
// #@event
// #@language ISO-LANGUAGE-CODE
// abc_string_name => Localized String

/** Uncomment me, if you need a textpack
$plugin['textpack'] = <<< EOT
#@admin
#@language en, en-gb, en-us
abc_sample_string => Sample String
abc_one_more => One more
#@language de
abc_sample_string => Beispieltext
abc_one_more => Noch einer
EOT;
**/
// End of textpack

if (!defined('txpinterface'))
	@include_once('zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h1. etc_flat

Work with flat file theme template files in Textpattern in debugging mode. Develop themes using the code editor of your choice.

_Note: etc_flat only acts on Textpattern page templates and forms, not on styles._


h2. Usage

h3. Setup

# Visit _Presentation › Themes_ and export your current theme to disk. It should be saved in the @/themes/theme-name/@ folder. Alternatively add a pre-made theme to the @/themes/@ folder and then import the theme from disk to Textpattern's admin interface.
# Visit _Presentation › Sections_ and make sure your theme is assigned to the relevant sections of your site.
# In _Admin › Plugins_ activate @etc_flat@.
# In _Admin › Preferences › Site_ set *Production status* to *Debugging*.

From now on, etc_flat by-passes the pages and forms in the database and pulls them in from the ‘flat files’ in your @/themes/theme-name/@ folder. Changes you make to files in your code editor are immediately available by refreshing the page.

If you visit _Presentation › Pages_ or _Presentation › Forms_, you will see a notice that etc_flat is active.

*Note: you must be logged in to see the changes.* Public visitors who are not logged in will see the theme using the pages and forms in the database.

h3. Deactivating

Simply switch the *Production status back to *Live* in _Admin › Preferences › Site_, and Textpattern will revert to using the pages and forms in the Textpattern database.

Alternatively, deactivate @etc_flat@ in _Admin › Plugins_.

h3. Incorporating flat file theme changes into your live site

When you are happy with your changes, visit _Presentation › Themes_ and import your theme from disk to the database by using the _Update from disk_ option. This will synchronise the database with the changes you made to your theme files. You can then disable etc_flat and switch the Production Status back to live and your theme updates will be active.


h2. Anomalies / Tips

h3. New page templates or forms

etc_flat will access the content of page templates or forms stored as files on disk. If you create new page templates or forms as files, Textpattern only become aware of them when you import your changes back to the database. Until then, new page templates or forms won't appear in the appropriate dropdowns (e.g. _Presentation › Sections_ or _Override form_).

*Solution*

Visit _Presentation › Themes_, check the checkbox next to your theme and choose _Update from disk_ to import your theme changes from disk to the database. From then on @etc_flat@ will be aware of those forms or page templates.

If you would rather not update all the database theme files (e.g. because they are live), then manually create a corresponding page template or form with the same name (it must be identical and assigned to the correct group) from the Textpattern admin interface.

h2. Changelog

- v0.1.3 – Handle error messages no longer suppressed by the @-operator in PHP 8+

- v0.1.2 – initial version by Oleg Loukianov, as posted on the "Textpattern forum":https://forum.textpattern.com/viewtopic.php?pid=310108#p310108.


# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

global $event, $production_status;

if ($production_status === 'debug' || $production_status !== 'live' && is_logged_in()) {
    register_callback('etc_flat', 'page.fetch');
    register_callback('etc_flat', 'form.fetch');

    if (txpinterface == 'admin' && in_array($event, array('page', 'form'))) {
        register_callback(function($event){
            global $event;
            echo announce(array('Serving '.gTxt($event.'s').' of '.get_pref('skin_editing', 'default').' theme from disk', E_WARNING));
        }, 'admin_side', 'body_end');
    }
}

function etc_flat($event, $step, $rs) {
    extract($rs);
    $page = false;
    $skin_dir = get_pref('skin_dir', 'themes');
    $sync = get_pref('skin_delete_from_database');

    if ($event == 'page.fetch') {
        is_readable($skin_dir.DS.$theme.DS.'pages'.DS.$name.'.txp') and $page = file_get_contents($skin_dir.DS.$theme.DS.'pages'.DS.$name.'.txp')
        or !$sync and $page = safe_field('user_html', 'txp_page', "name = '".doSlash($name)."' AND skin = '".doSlash($theme)."'");
    } else {
        empty($theme) or $skin = $theme;

        foreach (glob($skin_dir.DS.$skin.DS.'forms'.DS.'*', GLOB_ONLYDIR) as $dir) {
            if (is_readable($dir.DS.$name.'.txp') and $page = file_get_contents($dir.DS.$name.'.txp')) break;
        }

        $page or !$sync and $page = safe_field('Form', 'txp_form', "name = '".doSlash($name)."' AND skin = '".doSlash($skin)."'");
    }

    return $page;
}

# --- END PLUGIN CODE ---

?>
