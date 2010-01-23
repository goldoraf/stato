#!/bin/bash

# Namespace :
#   - Detect current namespace : "namespace A;"
#   Replace :
#   - \A\B\C => A_B_C
#   - B\C => A_B_C
#   - C => A_B_C
#   - \C => C
#   - Reflection : __NAMESPACE__
#
# Constant :
#   - __DIR__ 
#   - __NAMESPACE__

STATO53=`dirname $0`/stato;
STATO52=`dirname $0`/stato52;
cp -R $STATO53 $STATO53.backup 
#Here will be modified Stato source repository, to 
# make it compatible with this script
pushd $STATO53/library/Stato/I18n;
awk '/^\}/{n++}{print > "parts" n ".php" }' I18n.php
sed -i "s/^namespace//g" parts.php
sed -i "s/Stato/\\\Stato/g" parts.php
sed -i "s/^{//g" parts.php
sed -i "s/^\s\{4\}//g" parts.php
sed -i "s/^namespace \(.*\)/namespace \1;/g" parts1.php
sed -i "s/^{//g" parts1.php
sed -i "s/^}//g" parts1.php
sed -i "s/^\s\{4\}//g" parts1.php
rm -fr parts2.php
rm -fr I18n.php
popd
find $STATO53 -name Orm -exec rm -fr {} \+
sed -i "s/.*Orm.*AllTests::suite.*//g" $STATO53/tests/Stato/AllTests.php

rm -fr $STATO52
cp -R $STATO53 $STATO52

OIFS=$IFS
echo " Replacing __NAMESPACE__";
for PHP52 in `grep -lr "__NAMESPACE__" $STATO52`
do
  NAMESPACE=`grep "^\s*namespace " $PHP52`
  NAMESPACE=`echo $NAMESPACE | sed "s/\s*namespace\s*//g"`
  NAMESPACE=`echo $NAMESPACE | sed "s/ //g"`
  NAMESPACE=`echo $NAMESPACE | sed "s/;//g"`
  NAMESPACE=`echo $NAMESPACE | sed 's/\\\/\\\\\\\/g'`
  sed -i "s/__NAMESPACE__\s*\.\s*'/'$NAMESPACE/g" $PHP52
done

echo " Replacing __DIR__";
for PHP52 in `grep -lr "__DIR__" $STATO52`
do
  sed -i "s/__DIR__/dirname(__FILE__)/g" $PHP52
done

for PHP53 in `find $STATO53 -name *.php` 
do
  echo Migrating : $PHP53
  IFS=$'\n'
  # Extract the exact namespace from "namespace Stato\Package;" line
  NAMESPACE=`grep "^\s*namespace " $PHP53`
  NAMESPACE=`echo $NAMESPACE | sed "s/\s*namespace\s*//g"`
  NAMESPACE=`echo $NAMESPACE | sed "s/ //g"`
  PREFIX=$NAMESPACE
  NAMESPACE=`echo $NAMESPACE | sed "s/;//g"`
  NAMESPACE=`echo $NAMESPACE | sed 's/\\\/\\\\\\\/g'`
  QUOTEDNAMESPACE=`echo $NAMESPACE | sed 's/\\\/_/g'`
  REFLEXIONNS=`echo $NAMESPACE | sed 's/\\\\\\\/\\\\\\\\\\\(\\\\\\\\\\\)\\\?/g'`  
  # Build the class name prefix from namespace 
  # for exemple Stato\Package wille become Stato_Package
  PREFIX=`echo $PREFIX | sed "s/\//_/g"`
  PREFIX=`echo $PREFIX | sed 's/\\\/_/g'`
  PREFIX=`echo $PREFIX | sed 's/;/_/g'`

  #class
  for CLASS in `grep -riE "^\s*(abstract\s*)?(class|interface) " $PHP53` 
  do
    # Extract the classname from the class declaration line.
    CLASS=`echo $CLASS | sed "s/^\s*\(abstract\s*\)\?\(class\|interface\) //gi"`
    CLASS=`echo $CLASS | sed "s/\s*\(extends\|implements\) .*$//gi"`
    # Build the fulle classname
    FULLCLASS=$PREFIX$CLASS
    FULLPATH="$NAMESPACE\\\\$CLASS"
    echo "  $CLASS will become $FULLCLASS"
    echo "    Explicit and relative namespace invocation"
    for PHP52 in `find $STATO52 -name *.php`
    do
      # explicit namespace invocation replacement :
      # for exemple :
      # \Stato\Package\Class::Var =>  Stato_Package_Class
      sed -i "s/\\\\$FULLPATH/$FULLCLASS/g" $PHP52
      sed -i "s/'$FULLPATH'/'$FULLCLASS'/g" $PHP52
      # relative namespace invocation replacement :
      # for exemple :
      # namespace Stato;
      # [...]
      # Package\Class => Stato_Package_Class
      # For string manipulation \ is a pain in the *ss
      if [ "$NAMESPACE" != "" ]
      then
        CURRENT=`grep "^\s*namespace " $PHP52`
        CURRENT=`echo $CURRENT | sed "s/\s*namespace\s*//g"`
        CURRENT=`echo $CURRENT | sed "s/ //g"`
        CURRENT=`echo $CURRENT | sed 's/;//g'`
        CURRENT=`echo $CURRENT | sed 's/\\\/\\\\\\\/g'`
        CURRENT="$CURRENT\\\\"
        # For string manipulation \ is a pain in the *ss
        CURRENT=`echo $CURRENT | sed 's/\\\/_/g'`
        ISMATCH=`expr match $QUOTEDNAMESPACE $CURRENT`
        if [ "$ISMATCH" -gt "0" ]
        then 
          DIFF=`echo ${QUOTEDNAMESPACE#$CURRENT}`
          DIFF=`echo $DIFF | sed 's/_/\\\/g'`
          PARTIALPATH="$DIFF\\\\$CLASS"
          sed -i "s/\([^\]\)$PARTIALPATH\(\([^\]\)\|$\)/\1$FULLCLASS\3/g" $PHP52
          sed -i "s/\([^\]\)$PARTIALPATH\(\([^\]\)\|$\)/\1$FULLCLASS\3/g" $PHP52
        fi
      fi
    done  

    echo "    Implicit namespace invocation "
    # implicit namespace invocation replacement :
    # for exemple : 
    # namespace Stato\Package;
    # or 
    # use Stato\Package\Class;
    # new Class  => new Stato_Package_Class
    # 
    if [ "$NAMESPACE" != "" ]
    then
      for PHP52 in `grep -rl "^\s*\(namespace\s*$NAMESPACE\|use\s*$FULLPATH\)\s*;\s*" $STATO52`
      do
        sed -i "s/\([^\/']\)\<$CLASS\>\(\([^\/']\)\|$\)/\1$FULLCLASS\3/g" $PHP52
        sed -i "s/\([^\/']\)\<$CLASS\>\(\([^\/']\)\|$\)/\1$FULLCLASS\3/g" $PHP52
      done
    fi
  done
  # Try to replace reflexion mechanisme.
  # This is a while(!error) { do, take a look at mistake, fix } pattern
  if [ "$NAMESPACE" != "" ]
  then
    for PHP52 in `find $STATO52 -name *.php`
    do
      sed -i "s/'\(\\\\\)\?\(\\\\\)\?$REFLEXIONNS\(\\\\\)\?\(\\\\\)\?'/'$PREFIX'/g" $PHP52
    done
  fi  
  IFS=$OIFS
done

echo Removing namespace lines 
# Removing namespace line
for PHP52 in `grep -lr "^\s*namespace " $STATO52`
do
  NAMESPACE=`grep "^\s*namespace " $PHP52`
  NAMESPACE=`echo $NAMESPACE | sed 's/\\\/\\\\\\\/g'`  
  sed -i "s/$NAMESPACE//g" $PHP52
done
echo Removing use lines 
# Removing use line
for PHP52 in `grep -lr "^\s*use " $STATO52`
do
  # Possible multiple use lines
  IFS=$'\n'
  for USE in `grep "^\s*use " $PHP52`
  do
    USE=`echo $USE| sed 's/\\\/\\\\\\\/g'`  
    sed -i "s/$USE//g" $PHP52  
  done
  IFS=$OIFS
done
#Removing Root Class. This will be staticly done.
for PHP52 in `find $STATO52 -name "*.php"`
do
  sed -i "s/\\\\\(\<ArrayAccess\>\|\<ArrayObject\>\|\<DateTime\>\|\<Exception\>\|\<finfo\>\|\<FooController\>\|\<Iterator\>\|\<PDO\>\|\<PHPUnit_Extensions_Database_DataSet_CsvDataSet\>\|\<PHPUnit_Extensions_Database_TestCase\>\|\<PHPUnit_Extensions_OutputTestCase\>\|\<PHPUnit_Framework_TestCase\>\|\<PHPUnit_Framework_TestSuite\>\|\<RecursiveDirectoryIterator\>\|\<RecursiveIteratorIterator\>\|\<ReflectionClass\>\|\<ReflectionException\>\|\<ReflectionMethod\>\|\<TestForm\>\|\<XMLWriter\>\|\<__\>\|\<_p\>\|\<_f\>\|\<TestForm1\>\|\<TestForm2\>\|\<DateTimeZone\>\|\<stdClass\>\)/\1/g" $PHP52  
done

#FIXED: In tests/Stato/TestsHelper.php:30 : '__' should be '_'
sed -i "s/\\\\\\\/_/g" $STATO52/tests/Stato/TestsHelper.php
#FIXED: In library/Stato/Cli/CommandRunner.php:93 : '__' should be '_'
sed -i "s/\\\\\\\/_/g" $STATO52/library/Stato/Cli/CommandRunner.php
#FIXED: In tests/Stato/Cli/CommandTest.php:27 : Dummy <=> Stato_Cli_Command_Dummy in an echo.
sed -i "s/'\(.*\) Stato_Cli_Command_Dummy \(.*\)'/'\1 Dummy \2'/g" $STATO52/tests/Stato/Cli/files/commands/Foo.php
#FIXED: In tests/Stato/Cli/CommandTest.php:27 : Dummy <=> Stato_Cli_Command_Dummy in an echo.
sed -i "s/'\(.*\) Stato_Cli_Command_Dummy \(.*\)'/'\1 Dummy \2'/g" $STATO52/tests/Stato/Cli/files/commands/Dummy.php
#FIXED: Migrate Mailer class for dynamic call, instead of static call
sed -i "s/static function __callStatic/function __call/g" $STATO52/library/Stato/Mailer/Mailer.php
sed -i "s/^\(\s*\)\$class.*get_called_class.*$/\1\$this->reset();/g" $STATO52/library/Stato/Mailer/Mailer.php
sed -i "s/^.*\$mailer = .*$//g" $STATO52/library/Stato/Mailer/Mailer.php
sed -i "s/\$mailer->/\$this->/g" $STATO52/library/Stato/Mailer/Mailer.php
sed -i "s/UserMailer::/\$this->mailer->/g" $STATO52/tests/Stato/Mailer/MailerTest.php
sed -i "s/^\(\(\s*\).*->mail =.*\)$/\1\n\2\$this->mailer = new UserMailer();\n/g" $STATO52/tests/Stato/Mailer/MailerTest.php

#Reassembling I18n.php
pushd $STATO52/library/Stato/I18n;
cat parts.php > I18n.php
cat parts1.php >> I18n.php
/bin/rm -fr parts*.php
popd
/bin/rm -fr $STATO53 
mv $STATO53.backup $STATO53

#FIXME: In tests/Stato/Cli/FormTest.php:158 : 'xssxssjohn' is tested but code output 'xss/xssjohn'

echo FIXME: In tests/Stato/Cli/FormTest.php:158 : 'xssxssjohn' is tested but code output 'xss/xssjohn'
echo ==========================================================================
echo The Orm part is not migrated. Late static binding does not exist in php 5.2.
echo The mailer part cannot be staticly called anymore. __callStatic does not exist in php 5.2
echo To run tests you must run phpunit Stato_AllTests AllTests.php instead of phpunit AllTests.php
