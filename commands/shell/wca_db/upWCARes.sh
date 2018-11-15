#!/bin/bash

PATH=/usr/local/bin:$PATH

cd `dirname $0` || exit
baseUrl='https://www.worldcubeassociation.org/results/misc/'
staticPage='export.html'
localDir=`pwd`/
last=$localDir/last
[ -e $last ] && lastFileName=`cat $last` || lastFileName='aa'

sysType=`uname | grep -i darwin`
if [ -z $sysType ]
then
    grep=grep
    date=date
else
    grep=ggrep
    date=gdate
fi

fileName=`curl -k --compressed $baseUrl$staticPage 2>/dev/null|$grep -oP 'WCA_export\d+_\d{8}[0-9TZ]*\.sql\.zip'|head -1`
rm -f $localDir/WCA_export*_`$date --date='15 days ago' +%Y%m%d`*.sql.zip

if [ -z $fileName ] || [[ $lastFileName = $fileName ]]
then
	exit;
fi
if [ ! -e $localDir$fileName ]
then
    wget -q $baseUrl$fileName -O $localDir$fileName || exit
fi

dbConfig="$localDir/../../../config/common/wcaDb"
dbNum=`expr \( \`cat $dbConfig\` + 1 \) % 2`
dbName="wca_$dbNum"
dbConf=`[[ -f $localDir/my.local.cnf ]] && echo $localDir/my.local.cnf || echo $localDir/my.cnf`
sqlName='WCA_export.sql'
additionalSqlName='additional.sql'
yii=$localDir/../../../yii

unzip -qq -o $fileName $sqlName || exit
cat $sqlName $additionalSqlName | mysql --defaults-extra-file=$dbConf $dbName || exit
echo $fileName > $last
echo $dbNum > $dbConfig
$yii cache/flush-all
rm $localDir/$sqlName
