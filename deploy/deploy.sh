#!/usr/bin/env bash

svn co $SVN_REPOSITORY ./svn

rsync \
--exclude svn \
--exclude assets_svn \
--exclude deploy \
--exclude .git \
--exclude README.md \
--exclude .travis.yml \
-vaz ./* ./svn/trunk/

cp -r ./assets_svn/ ./svn/assets/

cd svn

svn add --force trunk
svn add --force assets

svn cp \
trunk tags/$TRAVIS_TAG \
--username $SVN_USERNAME \
--password $SVN_PASSWORD

# 6. Push SVN tag
svn ci \
--message "Release $TRAVIS_TAG" \
--username $SVN_USERNAME \
--password $SVN_PASSWORD \
--non-interactive