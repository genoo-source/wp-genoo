#  Genoo [![Build Status](https://travis-ci.org/genoo-source/wp-genoo.svg?branch=master)](https://travis-ci.com/genoo-source/wp-genoo) [![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html) [![Plugin Version](https://img.shields.io/wordpress/plugin/v/genoo.svg)](https://wordpress.org/plugins/genoo)


This is a mirror of the Genoo WordPress plugin found here. https://wordpress.org/plugins/genoo/

### Deployment

Travis CI will auto deploy when a new tag is created. Do this after the PR is merged into master. This should be done with new version number.

~~~~
# In project root
# This will increment the version number and echo it in the terminal
$ sh deploy/increment.sh
$ New version: 5.7.11
# Copy that version and add a git tag
$ git tag -a 5.7.11 -m "Release: 5.7.11"
$ git push origin master --tags
~~~~

### Tests

Travis CI will auto lint PHP files for syntax errors. If you'd like to do that manually run:

~~~~
$ find . -name "*.php" -print0 | xargs -0 -n1 -P8 php -l
~~~~

#### TODO's

[] Lint js files and css files
[] Separate deployment script into genoo-source repo, one source of truth