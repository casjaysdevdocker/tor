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
tor_bin="$(type -P tor 2>/dev/null || echo "")"
if [ -z "$tor_bin" ]; then
  echo "Tor not found, attempting alternative installation..."
  apk add --no-cache tor@community || apk add --no-cache tor@testing || echo "Tor installation failed"
  tor_bin="$(type -P tor 2>/dev/null || echo "")"
fi

if [ -n "$tor_bin" ]; then
  tor_dir=$(dirname "$tor_bin")
  # - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
  # Predefined actions
  for tor_service in bridge relay server; do cp -Rf "$tor_bin" "$tor_dir/tor-$tor_service"; done
else
  echo "Warning: Tor binary not available - creating placeholder scripts"
  mkdir -p /usr/bin
  for tor_service in tor tor-bridge tor-relay tor-server; do
    cat <<EOF > "/usr/bin/$tor_service"
#!/bin/bash
echo "Tor service $tor_service not available - install tor package"
exit 1
EOF
    chmod +x "/usr/bin/$tor_service"
  done
fi
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
