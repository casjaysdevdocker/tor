#!/usr/bin/env bash
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
##@Version           :  202501061127-git
# @@Author           :  CasjaysDev
# @@Contact          :  CasjaysDev <docker-admin@casjaysdev.pro>
# @@License          :  MIT
# @@ReadME           :
# @@Copyright        :  Copyright 2023 CasjaysDev
# @@Created          :  Mon Aug 28 06:48:42 PM EDT 2023
# @@File             :  06-post.sh
# @@Description      :  script to run post
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
LIST_INDEX_FILE="/usr/share/httpd/default/list.html"
RAW_URL="https://raw.githubusercontent.com/alecmuffett/real-world-onion-sites/refs/heads/master/README.md"
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# Predefined actions
__build_onions_html() {
	# temp file (POSIX-safe mktemp use)
	tmp_md="$(mktemp 2>/dev/null || printf '/tmp/rwos.%s' "$$")" || {
		printf '%s\n' "mktemp failed" >&2
		return 1
	}
	trap 'rm -f "$tmp_md"' 0 1 2 3 15

	# 1) Fetch README (UTF-8)
	if ! curl -fsSL "$RAW_URL" >"$tmp_md"; then
		printf '%s\n' "curl failed to fetch README" >&2
		return 2
	fi

	# 2) Emit full HTML
	{
		# --- HEAD (your exact head) ---
		cat <<'HTML_HEAD'
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" type="text/css" href="/index.css" />
  <title>Welcome to your tor site</title>
</head>
<body>
<main>
<pre>
HTML_HEAD

		# --- Body content pipeline ---
		# Step A: replace GitHub :shortcodes: with emoji
		# Step B: HTML-escape (&, <, >) to preserve literal layout
		# Step C: auto-link http(s) URLs (done after escaping so <a> survives)
		awk '
      BEGIN { OFS=""; RS="\n"; ORS="\n" }
      {
        gsub(":white_check_mark:", "✅")
        gsub(":closed_lock_with_key:", "🔐")
        gsub(":small_red_triangle:", "🔺")
        gsub(":key:", "🔑")
        gsub(":question:", "❓")
        gsub(":sos:", "🆘")
        gsub(":alarm_clock:", "⏰")
        gsub(":timer_clock:", "⏲️")
        gsub(":exclamation:", "❗")
        gsub(":eight_spoked_asterisk:", "✳️")
        gsub(":crystal_ball:", "🔮")
        gsub(":lock:", "🔒")
        gsub(":arrow_up:", "⬆️")
        gsub(":wrench:", "🔧")
        gsub(":new:", "🆕")
        print
      }
    ' "$tmp_md" |
			awk '
        # HTML-escape in correct order
        { gsub(/&/, "&amp;"); gsub(/</, "&lt;"); gsub(/>/, "&gt;"); print }
      ' |
			awk '
        # Auto-link URLs using POSIX awk (ERE), no gensub needed.
        # Allowed URL chars kept conservative; onion links are fine.
        {
          line = $0
          re = "https?://[A-Za-z0-9._~:/?#@!$&()*+,;=%-]+"
          out = ""
          while (match(line, re)) {
            pre = substr(line, 1, RSTART-1)
            url = substr(line, RSTART, RLENGTH)
            line = substr(line, RSTART+RLENGTH)
            out = out pre "<a href=\"" url "\" rel=\"noopener noreferrer\">" url "</a>"
          }
          $0 = out line
          print
        }
      '

		# --- TAIL ---
		cat <<'HTML_TAIL'
</pre>
<div><center>%s</center></div>\n' "Last uopdated on $(date +'%A, %B %d, %Y at %H:%M %Z')"
</main>
</body>
</html>
HTML_TAIL
	} >"$LIST_INDEX_FILE" || {
		printf '%s\n' "write failed: $LIST_INDEX_FILE" >&2
		return 3
	}

	printf 'Wrote %s\n' "$LIST_INDEX_FILE" >&2
}

# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# Main script
__build_onions_html
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# Set the exit code
#exitCode=$?
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
exit $exitCode
