#!/usr/bin/env bash

mkdir bin/docker/images/2-3-0

# Generate DockerFile
cp -f bin/docker/images/default/Dockerfile bin/docker/images/2-3-0/Dockerfile
cp -f bin/docker/images/default/entrypoint.sh bin/docker/images/2-3-0/entrypoint.sh

# Change the clause FROM
sed -i -e "s/FROM hipay\/hipay-magento2:2.1.10/FROM hipay\/hipay-magento2:2.3.0/" bin/docker/images/2-3-0/Dockerfile

# Generate docker_compose
cp -f docker-compose.acceptance.yml docker-compose.acceptance-2-3-0.yml

sed -i -e "s/images\/default/images\/2-3-0/" docker-compose.acceptance-2-3-0.yml
sed -i -e "s/\${DOCKER_STACK}-\${DOCKER_SERVICE}_web:\${CI_COMMIT_REF_SLUG}/\${DOCKER_STACK}-\${DOCKER_SERVICE}_web-2-3-0:\${CI_COMMIT_REF_SLUG}/" docker-compose.acceptance-2-3-0.yml
