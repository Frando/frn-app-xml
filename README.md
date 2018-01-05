# frn-app.php

Create XML for the freie-radios app. Actual data fetching and massaging is highly specific. Currently implementations exist for using the Drupal-based websites of Radio Dreyckland (rdl.de) and Radio FreeFM (freefm.de).

### Installation

    composer install

### Usage and configuration

    ./frn-app.php generate:rdl
    
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