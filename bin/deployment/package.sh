#!/usr/bin/env bash

zip -r hipay-fullservice-sdk-magento2-$1.zip * -x bin\* -x data\* -x web\* -x git\* -x docker-compose.* -x log\* src\* -x magento.sh -x circle.yml -x conf\*