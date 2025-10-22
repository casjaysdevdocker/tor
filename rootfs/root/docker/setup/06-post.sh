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
md2html() {
	# Emit a lightweight container; you can remove the outer <div> if you don’t want it.
	emit_open() {
		cat <<'EOF'
<div class="wrap md-body">
EOF
	}
	emit_close() {
		cat <<'EOF'
</div>
EOF
	}

	# AWK Markdown -> HTML (body only). Designed to handle the README’s structure.
	awk_prog='
BEGIN{
  in_code=0; in_ul=0; in_ol=0; in_blockquote=0; in_table=0; table_open=0;
  last_nonblank="";
  FS="";
}
function htmlesc(s){ gsub(/&/,"&amp;",s); gsub(/</,"&lt;",s); gsub(/>/,"&gt;",s); return s }

function linkify(s){
  # Images: ![alt](src)
  s = gensub(/!\[([^[\]]+)\]\(([^) \t]+)\)/, "<img alt=\"\\1\" src=\"\\2\" />", "g", s)
  # Links: [text](url)
  s = gensub(/\[([^[\]]+)\]\(([^) \t]+)\)/, "<a href=\"\\2\" rel=\"noopener noreferrer\">\\1<\\/a>", "g", s)
  # Inline code
  s = gensub(/`([^`]+)`/, "<code>\\1<\\/code>", "g", s)
  # Bold / italic (simple)
  s = gensub(/\*\*([^*]+)\*\*/, "<strong>\\1<\\/strong>", "g", s)
  s = gensub(/\*([^*]+)\*/, "<em>\\1<\\/em>", "g", s)
  # Autolink http(s)
  s = gensub(/(https?:\/\/[A-Za-z0-9._~:\/?#\\[\\]@!$&'\''()*+,;=%-]+)/, "<a href=\"\\1\" rel=\"noopener noreferrer\">\\1<\\/a>", "g", s)
  # Bare .onion (with optional path/query)
  s = gensub(/([A-Za-z2-7]{16,56}\\.onion([A-Za-z0-9._~:\/?#\\[\\]@!$&'\''()*+,;=%-]*)?)/, "<a href=\"http:\\/\\/\\1\" rel=\"noopener noreferrer\">\\1<\\/a>", "g", s)
  return s
}

function flush_p(){ if(pbuf!=""){ printf("<p>%s</p>\n", pbuf); pbuf="" } }
function close_lists(){ if(in_ul){ print "</ul>"; in_ul=0 } if(in_ol){ print "</ol>"; in_ol=0 } }
function close_blockquote(){ if(in_blockquote){ print "</blockquote>"; in_blockquote=0 } }

function table_close_fn(){ if(table_open){ print "</tbody></table>"; table_open=0 } in_table=0 }
function split_cells(line,  n,i){
  sub(/^ *\|/,"",line); sub(/\| *$/,"",line);
  n=split(line, C, /\|/);
  for(i=1;i<=n;i++){ gsub(/^ +| +$/,"",C[i]) }
  return n
}

{
  raw=$0
  if(raw ~ /[^[:space:]]/){ last_nonblank=raw }

  # Fenced code
  if(!in_code && raw ~ /^```/){ flush_p(); close_lists(); close_blockquote(); print "<pre><code>"; in_code=1; next }
  if(in_code){
    if(raw ~ /^```/){ print "</code></pre>"; in_code=0; next }
    print htmlesc(raw); next
  }

  # Blank line
  if(raw ~ /^[[:space:]]*$/){
    if(in_table==0){ flush_p(); close_lists(); close_blockquote() }
    next
  }

  # HR
  if(raw ~ /^ *(-{3,}|\*{3,}|_{3,}) *$/){ flush_p(); close_lists(); close_blockquote(); print "<hr />"; next }

  # Table separator row -> open table with the previous header line
  if(raw ~ /^ *\|? *:?-{3,}:? *(?:\| *:?-{3,}:? *)+\|? *$/){
    hdr=last_nonblank
    split_cells(hdr); print "<table><thead><tr>";
    for(i=1;i in C;i++){ printf("<th>%s</th>", linkify(htmlesc(C[i]))) }
    print "</tr></thead><tbody>"; in_table=1; table_open=1; next
  }
  if(in_table && raw ~ /\|/){
    split_cells(raw); printf("<tr>");
    for(i=1;i in C;i++){ printf("<td>%s</td>", linkify(htmlesc(C[i]))) }
    print "</tr>"; next
  }
  if(in_table){ table_close_fn() }

  # Blockquotes
  if(raw ~ /^ *> */){
    flush_p(); close_lists();
    if(!in_blockquote){ print "<blockquote>"; in_blockquote=1 }
    gsub(/^ *> */,"",raw); print "<p>" linkify(htmlesc(raw)) "</p>"; next
  } else if(in_blockquote){ close_blockquote() }

  # Headings
  if(raw ~ /^#{1,6} /){
    flush_p(); close_lists(); close_blockquote();
    m=match(raw,/^#{1,6}/); level=RLENGTH; text=substr(raw, level+2);
    printf("<h%d>%s</h%d>\n", level, linkify(htmlesc(text)), level); next
  }

  # Lists
  if(raw ~ /^ *([-*]) +/){
    flush_p(); if(!in_ul){ print "<ul>"; in_ul=1 }
    item=raw; sub(/^ *[-*] +/,"",item); print "<li>" linkify(htmlesc(item)) "</li>"; next
  }
  if(raw ~ /^ *[0-9]+\. +/){
    flush_p(); if(!in_ol){ print "<ol>"; in_ol=1 }
    item=raw; sub(/^ *[0-9]+\. +/,"",item); print "<li>" linkify(htmlesc(item)) "</li>"; next
  }
  if(in_ul && raw !~ /^ *([-*]) +/ && raw !~ /^[[:space:]]*$/){ close_lists() }
  if(in_ol && raw !~ /^ *[0-9]+\. +/ && raw !~ /^[[:space:]]*$/){ close_lists() }

  # Paragraph accumulation (soft-wrap)
  line = linkify(htmlesc(raw))
  if(pbuf==""){ pbuf=line } else { pbuf=pbuf " " line }
}
END{
  if(in_code){ print "</code></pre>" }
  if(in_table){ table_close_fn() }
  if(in_blockquote){ close_blockquote() }
  flush_p(); close_lists()
}
'

	emit_open
	curl -q -LSsf "$RAW_URL" | awk "$awk_prog" || return 1
	emit_close
}

# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# Main script
echo "Creating the onion web sites list"
cat >"$LIST_INDEX_FILE" <<'HTML'
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
HTML
md2html >>"$LIST_INDEX_FILE"
printf '<div><center>%s</center></div>\n' "Last uopdated on $(date +'%A, %B %d, %Y at %H:%M %Z')" >>"$LIST_INDEX_FILE"
printf '\n</body>\n</html>\n' >>"$LIST_INDEX_FILE"
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# Set the exit code
#exitCode=$?
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
exit $exitCode
