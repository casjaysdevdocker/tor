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
  in_code=0; code_fence=""; in_ul=0; in_ol=0; in_blockquote=0;
  in_table=0; table_open=0; last_nonblank="";
  FS="";
}

function htmlesc(s,   t){
  gsub(/&/,"&amp;",s); gsub(/</,"&lt;",s); gsub(/>/,"&gt;",s); return s
}

# Inline transforms: minimal but safe
function linkify(s,   before,after){
  # Images: ![alt](src)
  s = gensub(/!\[([^[\]]+)\]\(([^) \t]+)\)/, "<img alt=\"\\1\" src=\"\\2\" />", "g", s)
  # Links: [text](url)
  s = gensub(/\[([^[\]]+)\]\(([^) \t]+)\)/, "<a href=\"\\2\" rel=\"noopener noreferrer\">\\1<\\/a>", "g", s)
  # Inline code: `code`
  s = gensub(/`([^`]+)`/, "<code>\\1<\\/code>", "g", s)
  # Bold/italic (simple, non-nested)
  s = gensub(/\*\*([^*]+)\*\*/, "<strong>\\1<\\/strong>", "g", s)
  s = gensub(/\*([^*]+)\*/, "<em>\\1<\\/em>", "g", s)
  # Autolink http(s)
  s = gensub(/(https?:\/\/[A-Za-z0-9._~:\/?#\\[\\]@!$&'\''()*+,;=%-]+)/, "<a href=\"\\1\" rel=\"noopener noreferrer\">\\1<\\/a>", "g", s)
  # Bare .onion (with optional path/query)
  s = gensub(/([A-Za-z2-7]{16,56}\\.onion([A-Za-z0-9._~:\/?#\\[\\]@!$&'\''()*+,;=%-]*)?)/, "<a href=\"http:\\/\\/\\1\" rel=\"noopener noreferrer\">\\1<\\/a>", "g", s)
  return s
}

# Emit paragraph buffer if needed
function flush_p(){
  if(pbuf!=""){
    printf("<p>%s</p>\n", pbuf);
    pbuf="";
  }
}

# Close list/blockquote contexts
function close_lists(){
  if(in_ul){ print "</ul>"; in_ul=0 }
  if(in_ol){ print "</ol>"; in_ol=0 }
}
function close_blockquote(){
  if(in_blockquote){ print "</blockquote>"; in_blockquote=0 }
}

# Table helpers
function table_open_fn(){
  if(!table_open){ print "<table>"; table_open=1 }
}
function table_close_fn(){
  if(table_open){ print "</tbody></table>"; table_open=0 }
  in_table=0
}
function split_cells(line,  arr,n,i,cell){
  # strip leading/trailing |
  sub(/^ *\|/,"",line); sub(/\| *$/,"",line);
  n=split(line, arr, /\|/);
  for(i=1;i<=n;i++){
    gsub(/^ +| +$/,"",arr[i]); # trim
    arr[i]=arr[i];
  }
  return n
}

{
  raw=$0
  # Track last nonblank for table header detection
  if(raw ~ /[^[:space:]]/){ last_nonblank=raw }

  # Handle fenced code
  if(!in_code && raw ~ /^```/){
    flush_p(); close_lists(); close_blockquote();
    print "<pre><code>"; in_code=1; next
  }
  if(in_code){
    if(raw ~ /^```/){ print "</code></pre>"; in_code=0; next }
    print htmlesc(raw); next
  }

  # Blank line: end paragraphs/lists/quotes; not tables
  if(raw ~ /^[[:space:]]*$/){
    if(in_table==0){ flush_p(); close_lists(); close_blockquote() }
    next
  }

  # Horizontal rule
  if(raw ~ /^ *(-{3,}|\*{3,}|_{3,}) *$/){
    flush_p(); close_lists(); close_blockquote();
    print "<hr />"; next
  }

  # Table separator row => open table, emit header from last_nonblank
  if(raw ~ /^ *\|? *:?-{3,}:? *(?:\| *:?-{3,}:? *)+\|? *$/){
    # last_nonblank is header
    hdr=last_nonblank
    # Parse header into <thead>
    split_cells(hdr, H, nH)
    print "<table><thead><tr>"
    for(i=1;i<=nH;i++){ printf("<th>%s</th>", linkify(htmlesc(H[i]))) }
    print "</tr></thead><tbody>"
    in_table=1; table_open=1
    next
  }

  # Inside table: any row with at least one pipe (and not a fence)
  if(in_table && raw ~ /\|/){
    split_cells(raw, C, nC)
    printf("<tr>")
    for(i=1;i<=nC;i++){ printf("<td>%s</td>", linkify(htmlesc(C[i]))) }
    print "</tr>"
    next
  }
  # Exiting table block if we hit a non-pipe line
  if(in_table){
    table_close_fn()
  }

  # Blockquotes
  if(raw ~ /^ *> */){
    flush_p(); close_lists();
    if(!in_blockquote){ print "<blockquote>"; in_blockquote=1 }
    gsub(/^ *> */,"",raw)
    print "<p>" linkify(htmlesc(raw)) "</p>"
    next
  }else{
    if(in_blockquote && raw !~ /^ *> */){ close_blockquote() }
  }

  # Headings
  if(raw ~ /^#{1,6} /){
    flush_p(); close_lists(); close_blockquote();
    m=match(raw,/^#{1,6}/); level=RLENGTH
    text=substr(raw, level+2)
    printf("<h%d>%s</h%d>\n", level, linkify(htmlesc(text)), level)
    next
  }

  # Lists
  if(raw ~ /^ *([-*]) +/){
    flush_p()
    if(!in_ul){ print "<ul>"; in_ul=1 }
    item=raw; sub(/^ *[-*] +/,"",item)
    print "<li>" linkify(htmlesc(item)) "</li>"
    next
  }
  if(raw ~ /^ *[0-9]+\. +/){
    flush_p()
    if(!in_ol){ print "<ol>"; in_ol=1 }
    item=raw; sub(/^ *[0-9]+\. +/,"",item)
    print "<li>" linkify(htmlesc(item)) "</li>"
    next
  }
  # If we switch list types or leave lists
  if(in_ul && raw !~ /^ *([-*]) +/ && raw !~ /^[[:space:]]*$/){ close_lists() }
  if(in_ol && raw !~ /^ *[0-9]+\. +/ && raw !~ /^[[:space:]]*$/){ close_lists() }

  # Default: paragraph (accumulate soft-wrap into one <p>)
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
printf '\n</body>\n</html>\n' >>"$LIST_INDEX_FILE"
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# Set the exit code
#exitCode=$?
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
exit $exitCode
