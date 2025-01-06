#!/usr/bin/env bash
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
##@Version           :  202501061127-git
# @@Author           :  CasjaysDev
# @@Contact          :  CasjaysDev <docker-admin@casjaysdev.pro>
# @@License          :  MIT
# @@ReadME           :
# @@Copyright        :  Copyright 2023 CasjaysDev
# @@Created          :  Mon Aug 28 06:48:42 PM EDT 2023
# @@File             :  05-custom.sh
# @@Description      :  script to run custom
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
tor_bin="$(type -p tor)"
for s in server relay bridge hidden; do
  cp -Rf tor tor-$s
done
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# Predefined actions
[ -f "/etc/privoxy/trust.new" ] && mv -f /etc/privoxy/trust.new /etc/privoxy/trust
[ -f "/etc/privoxy/user.action.new" ] && mv -f /etc/privoxy/user.action.new /etc/privoxy/user.action
[ -f "/etc/privoxy/user.filter.new" ] && mv -f /etc/privoxy/user.filter.new /etc/privoxy/user.filter
[ -f "/etc/privoxy/default.action.new" ] && mv -f /etc/privoxy/default.action.new /etc/privoxy/default.action
[ -f "/etc/privoxy/default.filter.new" ] && mv -f /etc/privoxy/default.filter.new /etc/privoxy/default.filter
[ -f "/etc/privoxy/match-all.action.new" ] && mv -f /etc/privoxy/match-all.action.new /etc/privoxy/match-all.action
[ -f "/etc/privoxy/regression-tests.action.new" ] && mv -f /etc/privoxy/regression-tests.action.new /etc/privoxy/regression-tests.action
rm -Rf /etc/privoxy/*.new /etc/tor/*.sample
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# Main script

# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# Set the exit code
#exitCode=$?
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
exit $exitCode
