#!/bin/bash
if [ -e /etc/amportal.conf ]; then

DBNAME=`cat /etc/amportal.conf | sed 's/ //g' | grep ^AMPDBNAME | cut -d= -f2 | tail -n1`
DBUSER=`cat /etc/amportal.conf | sed 's/ //g' | grep ^AMPDBUSER | cut -d= -f2 | tail -n1`
DBPASS=`cat /etc/amportal.conf | sed 's/ //g' | grep ^AMPDBPASS | cut -d= -f2 | tail -n1`
DBHOST=`cat /etc/amportal.conf | sed 's/ //g' | grep ^AMPDBHOST | cut -d= -f2 | tail -n1`
AMPEXTENSIONS=`cat /etc/amportal.conf | sed 's/ //g' | grep ^AMPEXTENSION | cut -d= -f2 | tail -n1`

AMPDIR=`cat /etc/amportal.conf | sed 's/ //g' | grep ^AMPWEBROOT | cut -d= -f2 | tail -n1`
AMPDIR=$AMPDIR/admin/modules/framework/module.xml

AMPVERSION=`cat $AMPDIR | grep version | sed -e 's/<[^>]*>//g' | cut -b 2,4`

if [ $AMPVERSION -gt 25 ]; then
QUEUECONTEXT="from-queue"
else
QUEUECONTEXT="from-internal"
fi


FOP2PLUGIN=0

# Verify if the fop2 plugin table exists
for A in `mysql -NB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "SHOW tables FROM asterisk LIKE 'fop2users'"`
do
let FOP2PLUGIN=FOP2PLUGIN+1
done


if [ "$FOP2PLUGIN" -gt 0 ]; then
#Configuration from FreePBX plugin

if [ "${AMPEXTENSIONS}" != "deviceanduser" ]; then
#si no tiene device and user, exclusimos los USER/
mysql -ENB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e \
    "SELECT device AS channel,type,if(type<>'trunk',exten,'') AS extension,\
    label,mailbox,context,'$QUEUECONTEXT' as queuecontext,concat('*',mailbox) AS extenvoicemail, \
    privacy,\`group\`,IF(type='trunk',IF(email<>'',concat('splitme-',email),''),email) as email, \
    queuechannel,originatechannel FROM fop2buttons WHERE device NOT LIKE 'USER/%' AND device<>'' \
    ORDER BY type,exten" | sed '/\*\*/d' | sed 's/: /=/g' | sed '/.*=$/d' | while read LINEA
do
echo $LINEA | sed 's/^channel=\(.*\)/\n[\1]/g'
echo $LINEA | grep -qi "^email=splitme"
if [ $? = 0 ]; then
RANGE=`echo $LINEA | sed 's/^email=splitme-//g' | sed 's/-/ /g'`
for ZAPNUM in `seq $RANGE`
do
echo "channel=DAHDI/$ZAPNUM"
echo "channel=ZAP/$ZAPNUM"
done
fi
done

else

mysql -ENB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e \
    "SELECT if(type='extension',CONCAT('USER/',exten),device) AS channel,type,if(type<>'trunk',exten,' ') AS extension,\
    label,mailbox,context,'$QUEUECONTEXT' as queuecontext,concat('*',mailbox) AS extenvoicemail, \
    privacy,\`group\`,IF(type='trunk',IF(email<>'',concat('splitme-',email),''),email) as email, \
    queuechannel,originatechannel FROM fop2buttons WHERE device<>'' ORDER BY type,exten" | \
    sed '/\*\*/d' | sed 's/: /=/g' | sed '/.*=$/d' | while read LINEA
do
echo $LINEA | sed 's/^channel=\(.*\)/\n[\1]/g'
echo $LINEA | grep -qi "^email=splitme"
if [ $? = 0 ]; then
RANGE=`echo $LINEA | sed 's/^email=splitme-//g' | sed 's/-/ /g'`
for ZAPNUM in `seq $RANGE`
do
echo "channel=DAHDI/$ZAPNUM"
echo "channel=ZAP/$ZAPNUM"
done
fi
done
fi

#PARKSLOT=`/usr/sbin/asterisk -rx "dialplan show parkedcalls" | grep "=>" | cut -d= -f1 | sed s/\'//g | sed 's/ //g'`
#if [ "X${PARKSLOT}" != "X" ]; then
#echo
#echo "[PARK/default]"
#echo "extension=${PARKSLOT}"
#echo "context=parkedcalls"
#echo "type=park"
#echo "Label=Park ${PARKSLOT}"
#echo
#fi


else
#Configuration from FreePBX without plugin

if [ "${AMPEXTENSIONS}" != "deviceanduser" ]; then
# SIP EXTENSIONS
mysql -ENB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "select concat('SIP/',extension) as channel,extension,name as label,s1.data as mailbox,s2.data as context,'$QUEUECONTEXT' as queuecontext,concat('*',s1.data) as extenvoicemail from users as u left join sip as s1 on u.extension=s1.id and s1.keyword='mailbox' left join sip as s2 on u.extension=s2.id where s2.keyword='context' order by extension" | sed '/\*\*/d' | sed 's/: /=/g' | while read LINEA
do
echo $LINEA | sed 's/channel=\(.*\)/\n[\1]\ntype=extension/g'
done

# IAX2 EXTENSIONS
mysql -ENB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "select concat('IAX2/',extension) as channel,extension,name as label,s1.data as mailbox,s2.data as context,'$QUEUECONTEXT' as queuecontext,concat('*',s1.data) as extenvoicemail from users as u left join iax as s1 on u.extension=s1.id and s1.keyword='mailbox' left join iax as s2 on u.extension=s2.id where s2.keyword='context' order by extension" | sed '/\*\*/d' | sed 's/: /=/g' | while read LINEA
do
echo $LINEA | sed 's/channel=\(.*\)/\n[\1]\ntype=extension/g'
done

else

# FREEPBX DEVICEANDUSER
mysql -ENB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "select concat('USER/',extension) as channel, extension, name as label, concat(extension,'@',voicemail) as mailbox, 'from-internal' as context, '$QUEUECONTEXT' as queuecontext,concat('*',extension,'@from-internal') as extenvoicemail from users order by extension" | sed '/\*\*/d' | sed 's/: /=/g' | while read LINEA
do
echo $LINEA | sed 's/channel=\(.*\)/\n[\1]\ntype=extension\n/g'
done

fi

# SIP TRUNKS
mysql -ENB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "select concat('SIP/',s1.data) as trunk from sip left join sip as s1 on sip.id=s1.id and s1.keyword='account' where sip.keyword='host' and sip.data<>'dynamic'" | sed '/\*\*/d' | sed 's/: /=/g' | while read LINEA
do
echo $LINEA | sed 's/trunk=\(.*\)/\n[\1]\ntype=trunk\nlabel=\1/g'
done

# IAX2 TRUNKS
mysql -ENB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "select concat('IAX2/',s1.data) as trunk from iax left join iax as s1 on iax.id=s1.id and s1.keyword='account' where iax.keyword='host' and iax.data<>'dynamic'" | sed '/\*\*/d' | sed 's/: /=/g' | while read LINEA
do
echo $LINEA | sed 's/trunk=\(.*\)/\n[\1]\ntype=trunk\nlabel=\1/g'
done

#mysql -ENB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "select extension,context,descr as label from extensions where application='Queue';" | sed '/\*\*/d' | sed 's/: /=/g' | while read LINEA
mysql -ENB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "select extension,'ext-queues',descr as label from queues_config order by extension" | sed '/\*\*/d' | sed 's/: /=/g' | while read LINEA
do
echo $LINEA | sed 's/extension=\(.*\)/\n[QUEUE\/\1]\ntype=queue\nextension=\1/g'
done

mysql -ENB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "select exten as extension,'ext-meetme' as context,description as label from meetme" | sed '/\*\*/d' | sed 's/: /=/g' | while read LINEA
do
echo $LINEA | sed 's/extension=\(.*\)/\n[CONFERENCE\/\1]\ntype=conference\nextension=\1/g'
done


DAHDI=`/usr/sbin/asterisk -rx "zap show channels" | grep -v from-internal | grep -v pseudo | grep -v Language | awk '{print $1}' | head -n 1` 
if [ "X${DAHDI}" != "X" ]; then
echo
echo "[DAHDI/$DAHDI]"
echo "type=trunk"
echo "label=DAHDI"

for LIN in `/usr/sbin/asterisk -rx "zap show channels" | grep -v from-internal | grep from | awk '{print $1}'`
do
echo "channel=ZAP/$LIN";
done
for LIN in `/usr/sbin/asterisk -rx "dahdi show channels" | grep -v from-internal | grep from | awk '{print $1}'`
do
echo "channel=DAHDI/$LIN";
done

fi

PARKSLOT=`/usr/sbin/asterisk -rx "dialplan show parkedcalls" | grep "=>" | cut -d= -f1 | sed s/\'//g | sed 's/ //g'`
if [ "X${PARKSLOT}" != "X" ]; then
echo
echo "[PARK/default]"
echo "extension=${PARKSLOT}"
echo "context=parkedcalls"
echo "type=park"
echo "Label=Park ${PARKSLOT}"
echo
fi

fi

else
echo "Unable to find /etc/amportal.conf"
fi

