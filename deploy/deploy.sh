#!/usr/bin/env bash

# Check if required commands are available
command -v svn >/dev/null 2>&1 || { echo "Error: svn is not installed." >&2; exit 1; }
command -v rsync >/dev/null 2>&1 || { echo "Error: rsync is not installed." >&2; exit 1; }

# Checkout SVN repository
svn co $SVN_REPOSITORY ./svn

# Create necessary directories
mkdir -p ./svn/trunk ./svn/assets

# Sync files to SVN trunk
rsync \
--exclude svn \
--exclude assets_svn \
--exclude deploy \
--exclude .git \
--exclude README.md \
--exclude .travis.yml \
-vaz ./* ./svn/trunk/

# Copy assets
cp -r ./assets_svn ./svn/assets/

# Change to SVN directory
cd svn || { echo "Error: Failed to change to svn directory."; exit 1; }


# Add files to SVN
svn add --force trunk
svn add --force assets


# Tag the release
svn cp \
trunk tags/$TRAVIS_TAG \
--username $SVN_USERNAME \
--password $SVN_PASSWORD

# Commit changes
svn ci \
--message "Release $TRAVIS_TAG" \
--username $SVN_USERNAME \
--password $SVN_PASSWORD \
--non-interactive
