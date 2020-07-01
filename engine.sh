#!/bin/bash
SCRIPT=`realpath $0`
SCRIPT_PATH=`dirname $SCRIPT`

cd $SCRIPT_PATH/docker
docker-compose exec -T engine /home/comely-io/engine/src/console $@
cd ../
