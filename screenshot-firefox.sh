#!/bin/bash

export DISPLAY=":7"
/usr/bin/firefox -display :7 --geometry 1024x1024+0+0 --screen 0 "$1" &
/bin/sleep 10
/usr/bin/import -window root -display :7 -crop 1000x1000+4+115 +repage -resize 150x150 "$2"
killall firefox-bin