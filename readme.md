# etc_flat (Laravel Valet version)

**Use this version when developing with [Laravel Valet](https://laravel.com/docs/valet)**.

Work with flat file theme template files in Textpattern in debugging mode. Develop themes using the code editor of your choice.

_Note: `etc_flat` only acts on Textpattern page templates and forms, not on styles._


## Usage


### Setup

1. Visit _Presentation › Themes_ and export your current theme to disk. It should be saved in the `/themes/theme-name/` folder. Alternatively add a pre-made theme to the `/themes/` folder and then import the theme from disk to Textpattern's admin interface.
2. Visit _Presentation › Sections_ and make sure your theme is assigned to the relevant sections of your site.
3. In _Admin › Plugins_ activate `etc_flat`.
4. In _Admin › Preferences › Site_ set **Production status** to **Debugging**.

From now on, etc_flat by-passes the pages and forms in the database and pulls them in from the ‘flat files’ in your `/themes/theme-name/` folder. Changes you make to files in your code editor are immediately available by refreshing the page.

If you visit _Presentation › Pages_ or _Presentation › Forms_, you will see a notice that etc_flat is active.

**Note: you must be logged in to see the changes.** Public visitors who are not logged in will see the theme using the pages and forms in the database.


### Deactivating

Simply switch the **Production status** back to **Live** in _Admin › Preferences › Site_, and Textpattern will revert to using the pages and forms in the Textpattern database.

Alternatively, deactivate `etc_flat` in _Admin › Plugins_.


### Incorporating flat file theme changes into your live site

When you are happy with your changes, visit _Presentation › Themes_ and import your theme from disk to the database by using the _Update from disk_ option. This will synchronise the database with the changes you made to your theme files. You can then disable etc_flat and switch the Production Status back to live and your theme updates will be active.


## Anomalies / Tips


### New page templates or forms

etc_flat will access the content of page templates or forms stored as files on disk. If you create new page templates or forms as files, Textpattern only become aware of them when you import your changes back to the database. Until then, new page templates or forms won't appear in the appropriate dropdowns (e.g. _Presentation › Sections_ or _Override form_).

**Solution**

Visit _Presentation › Themes_, check the checkbox next to your theme and choose _Update from disk_ to import your theme changes from disk to the database. From then on `etc_flat` will be aware of those forms or page templates.

If you would rather not update all the database theme files (e.g. because they are live), then manually create a corresponding page template or form with the same name (it must be identical and assigned to the correct group) from the Textpattern admin interface.


## Changelog

- v0.1.3 – modified paths when working with [Laravel Valet](https://laravel.com/docs/valet) und a parked `~/Sites` folder. (Changes / jcr)
- v0.1.2 – initial version by Oleg Loukianov, as posted on the [Textpattern forum](https://forum.textpattern.com/viewtopic.php?pid=310108#p310108).


