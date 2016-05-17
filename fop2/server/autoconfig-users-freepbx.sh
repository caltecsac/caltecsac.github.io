#!/bin/bash

if [ -e /etc/amportal.conf ]; then

DBNAME=`cat /etc/amportal.conf | sed 's/ //g' | grep ^AMPDBNAME | cut -d= -f2 | tail -n1`
DBUSER=`cat /etc/amportal.conf | sed 's/ //g' | grep ^AMPDBUSER | cut -d= -f2 | tail -n1`
DBPASS=`cat /etc/amportal.conf | sed 's/ //g' | grep ^AMPDBPASS | cut -d= -f2 | tail -n1`
DBHOST=`cat /etc/amportal.conf | sed 's/ //g' | grep ^AMPDBHOST | cut -d= -f2 | tail -n1`
AMPEXTENSIONS=`cat /etc/amportal.conf | sed 's/ //g' | grep ^AMPEXTENSION | cut -d= -f2 | tail -n1`

eval `cat /etc/asterisk/voicemail.conf | grep -v "^\;" | grep "=>" | cut -d, -f1 | sed 's/ //g' | sed  's/\([^=]*\)=>\(.*\)/Fop2Clave[\1]=\2;/g'`

FOP2PLUGIN=0

# Verify if the fop2 plugin table exists
for A in `mysql -NB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "SHOW tables FROM asterisk LIKE 'fop2users'"`
do
let FOP2PLUGIN=FOP2PLUGIN+1
done

if [ "$FOP2PLUGIN" -gt 0 ]; then
mysql -NB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "set @@group_concat_max_len=32768; SELECT CONCAT('group=',name,':',GROUP_CONCAT(device)) FROM fop2groups LEFT JOIN fop2GroupButton ON fop2groups.name=fop2GroupButton.group_name LEFT JOIN fop2buttons on id_button=fop2buttons.id GROUP BY name" |  while read LINEA
do
echo $LINEA
done

mysql -NB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "set @@group_concat_max_len=32768; SELECT CONCAT('user=',fop2users.exten,':',if(secret='','EMPTYSECRET',secret),':',permissions,':'),GROUP_CONCAT(if(name is NULL,'',name)) FROM fop2users LEFT OUTER JOIN fop2UserGroup ON fop2users.exten=fop2UserGroup.exten LEFT OUTER JOIN fop2groups ON id_group=fop2groups.id GROUP BY fop2users.exten" | while read LINEA
do
MYEXTEN=`echo $LINEA | cut -d: -f1 | cut -d\= -f2`
echo -n $LINEA | sed 's/EMPTYSECRET/'${Fop2Clave[${MYEXTEN}]}'/g' | sed 's/: /:/g'
echo
done

echo "buttonfile=autobuttons.cfg"
else
#Generacion de usuarios sin freepbx plugin
for A in `cat /etc/asterisk/voicemail.conf | grep "=>" | cut -d, -f1 | sed 's/ => /:/g'`; do echo user=$A:all; done
echo "buttonfile=autobuttons.cfg"

fi
else
#Generacion de usuarios sin freepbx plugin
for A in `cat /etc/asterisk/voicemail.conf | grep "=>" | cut -d, -f1 | sed 's/ => /:/g'`; do echo user=$A:all; done
echo "buttonfile=autobuttons.cfg"

fi

