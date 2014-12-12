#!/bin/bash

export DISPLAY=":7"
/usr/bin/opera -display :7 -geometry 1024x1024+0+0 -nomail -nosession -fullscreen "$1" > /dev/null 2> /dev/null &
/bin/sleep 10
/usr/bin/import -window root -display :7 -resize 100x100 "$2"
/usr/bin/killall opera
