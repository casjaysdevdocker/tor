#!/usr/bin/env bash
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
##@Version           :  202408270903-git
# @@Author           :  CasjaysDev
# @@Contact          :  CasjaysDev <docker-admin@casjaysdev.pro>
# @@License          :  MIT
# @@ReadME           :
# @@Copyright        :  Copyright 2023 CasjaysDev
# @@Created          :  Mon Aug 28 06:48:42 PM EDT 2023
# @@File             :  03-files.sh
# @@Description      :  script to run files
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# shellcheck shell=bash
# shellcheck disable=SC2016
# shellcheck disable=SC2031
# shellcheck disable=SC2120
# shellcheck disable=SC2155
# shellcheck disable=SC2199
# shellcheck disable=SC2317
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# Set bash options
set -o pipefail
[ "$DEBUGGER" = "on" ] && echo "Enabling debugging" && set -x$DEBUGGER_OPTIONS
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# Set env variables
exitCode=0
rm -Rf /etc/unbound/*
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# Predefined actions
if [ -d "/tmp/bin" ]; then
  mkdir -p "/usr/local/bin"
  for bin in "/tmp/bin"/*; do
    name="$(basename -- "$bin")"
    echo "Installing $name to /usr/local/bin/$name"
    copy "$bin" "/usr/local/bin/$name"
    chmod -f +x "/usr/local/bin/$name"
  done
fi
unset bin
if [ -d "/tmp/var" ]; then
  for var in "/tmp/var"/*; do
    name="$(basename -- "$var")"
    echo "Installing $var to /var/$name"
    if [ -d "$var" ]; then
      mkdir -p "/var/$name"
      copy "$var/." "/var/$name/"
    else
      copy "$var" "/var/$name"
    fi
  done
fi
unset var
if [ -d "/tmp/etc" ]; then
  for config in "/tmp/etc"/*; do
    name="$(basename -- "$config")"
    echo "Installing $config to /etc/$name"
    if [ -d "$config" ]; then
      mkdir -p "/etc/$name"
      copy "$config/." "/etc/$name/"
      mkdir -p "/usr/local/share/template-files/config/$name"
      copy "$config/." "/usr/local/share/template-files/config/$name/"
    else
      copy "$config" "/etc/$name"
      copy "$config" "/usr/local/share/template-files/config/$name"
    fi
  done
fi
unset config
if [ -d "/tmp/data" ]; then
  for data in "/tmp/data"/*; do
    name="$(basename -- "$data")"
    echo "Installing $data to /usr/local/share/template-files/data"
    if [ -d "$data" ]; then
      mkdir -p "/usr/local/share/template-files/data/$name"
      copy "$data/." "/usr/local/share/template-files/data/$name/"
    else
      copy "$data" "/usr/local/share/template-files/data/$name"
    fi
  done
fi
unset data
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# Main script

# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# Set the exit code
#exitCode=$?
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
exit $exitCode
