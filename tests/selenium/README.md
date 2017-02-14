# Selenium tests

Please see tests/selenium/README.md file in mediawiki/core repository.

## Usage

Set up MediaWiki-Vagrant:

    cd mediawiki/vagrant
    vagrant up
    vagrant roles enable mobilefrontend relatedarticles
    vagrant provision

From mediawiki/core folder:

    echo 'include_once "$IP/extensions/RelatedArticles/tests/browser/LocalSettings.php";' >> LocalSettings.php

Run both mediawiki/core and RelatedArticles tests from mediawiki/core folder:

    npm run selenium

To run only RelatedArticles tests in one terminal window or tab start Chromedriver:

    chromedriver --url-base=/wd/hub --port=4444

In another terminal tab or window go to mediawiki/core folder:

    ./node_modules/.bin/wdio tests/selenium/wdio.conf.js --spec extensions/RelatedArticles/tests/selenium/specs/*.js

Run only one RelatedArticles test file from mediawiki/core:

    ./node_modules/.bin/wdio tests/selenium/wdio.conf.js --spec extensions/RelatedArticles/tests/selenium/specs/readmore.js
