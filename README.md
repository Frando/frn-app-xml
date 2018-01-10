# frn-app-xml.php

Create XML for the freie-radios app. Actual data fetching and massaging is highly specific. Currently implementations exist for using the Drupal-based websites of Radio Dreyckland (rdl.de) and Radio FreeFM (freefm.de).

### Installation

    composer install

### Usage and configuration

    ./frn-app-xml.php generate:rdl
    
Options:

    --id=ID                       Only include these IDs 
    --save-path=SAVE-PATH         Path to save the XML to
    -r, --drupal-root=DRUPAL-ROOT Path to Drupal root 
    -l, --drupal-url=DRUPAL-URL   Drupal base URL
   

In `.env`

    RDL_DRUPAL_ROOT=/var/www
    RDL_DRUPAL_URL=rdl.de
    SAVE_PATH=/var/www/rdl-frn.xml
    
### Extension

See `src/Rdl` and `src/FreeFm` for two implementations. Both are Drupal-based. For other PHP based CMS the implementation could look similar (see `DrupalAdapterBase.php`).

The implementation of each backend is three-fold:

1) `Command`: Symfony console command. Extend `GenreateCommandBase.php` and just call `createXml`, passing an `XmlCreator`.

2) `XmlCreator`: A class implementing `XmlCreatorInterface`. Extend `XmlCreatorBase` and implement the stub methods, returning a `\DOMElement` for the different sections of the XML. `getBroadcast` is called for each broadcast returned from the `Adapter` and should return a `\DOMElement` for each.

3) `Adapter`: Return a list of broadcasts (shows) to be included in the XML. All logic and communication towards a specific backend should happen here.

Copying the existing implementations and changing things where required should be the easiest.

Note that commands are not auto-discovered, but have to be registered with the application in `frn-app-xml.php`.