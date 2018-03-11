# OLS site configuration script
# ======================================================================

# This script is used to configure the OLS site code.
# Make an editable copy of this file, put it *outside* your web
# server's doc root, and edit that file to configure your OLS system.

# Set this file's variables to values that are appropriate for your
# system. In some cases, the setting that already appears here may
# be satisfactory. However, you will definitely need to change any value
# that refers to 'example.org', and any value that's a password.

# SECURITY WARNING:
# Because this file will contain various passwords, you should be
# careful not to allow it to be seen by unprivileged users. In
# particular, don't put it (or leave it) under your server's doc root.

# This file (or rather, your edited copy of it) is sourced by 'configure'.
# That is a Bourne Shell script, so you can use any syntax that /bin/bash
# allows.  However, in typical usage, you would merely assign literal
# values to shell variables.

# Bug/limitation: if a variables value contains an apostrophe, the
# configuration process will not work correctly. (Note that it's fine to
# use apostrophes to delimit string literals; they aren't part of the
# value.)

# XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

# Database access
# ---------------

# These parameters specify database connection and configuration settings.
# See SETUP/installation.txt for instructions on how to create the database
# and user.

_HOSTNAME=localhost
_DB_USERNAME=PICK_A_USER_NAME
_DB_PASSWORD=PICK_A_HARD_PASSWORD
_DB_NAME=PICK_A_DB_NAME

# XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

# Identifying the Site
# --------------------

# (Bug/limitation: The values for the next three variables should avoid
# three characters that are special to HTML/XML: < > &.)

# Something like 'Open Library System' would be good. It should
# make sense in contexts like 'Welcome to %s', and 'the %s website'.
_SITE_NAME=PICK_A_NAME

# Something like 'OLS' would be good.
_SITE_ABBREVIATION=PICK_AN_ABBREVIATION

# This is the base URL where the code is available on your web server.
# Do not include the trailing slashl.
_SITE_BASE_URL=http://something.or.other.com/ols

# XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

# Archive paths
# -------------

# Base filesystem path to the archive files.
# This value is prefixed to the value of the `page_image_location` field
# in the catalog table for an archived item.
# The result is treated as the full absolute path to all of the files
# for that item.
# If the paths stored in your database are already absolute, this value
# should be set to ''.
_ARCHIVE_PATH_PREFIX=''

# Archive page images subdirectory.
# This value is suffixed to the value of the `page_image_location` field
# in the catalog table for an archived item.
# The result is treated as the full absolute path to the directory which
# contains ONLY the page images for that item.
_ARCHIVE_PAGES_SUBDIR=''

# XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

# Parent organization
# -------------------

# Base URL for the "parent" site.
# This should be set to the URL of the organization which produces the
# files stored in your archive.
_PARENT_SITE_BASE_URL=""

# Name of the "parent" site.
# This should be set to the formal name of the organization given above.
_PARENT_SITE_NAME=""

# Abbreviated name of the "parent" site.
# The abbreviated formal name of the organization given above.
_PARENT_SITE_ABBR=""

# XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

# Misc
# ----

# Absolute filesystem path for the PG catalog RDF files.
# Set this variable to "" if you do not have a local copy of the PG
# Catalog in RDF form.
# See https://www.gutenberg.org/wiki/Gutenberg:Feeds for more information.
_PG_RDF_CATALOG_DIR=""
