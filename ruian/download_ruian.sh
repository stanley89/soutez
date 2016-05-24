#!/bin/bash

for i in {500011..599999}
do
  wget http://vdp.cuzk.cz/vymenny_format/soucasna/20160331_OB_$i\_UKSH.xml.gz
done

wget http://vdp.cuzk.cz/vymenny_format/specialni/20160403_ST_UVOH.xml.gz
wget http://vdp.cuzk.cz/vymenny_format/soucasna/20160331_ST_UKSH.xml.gz
