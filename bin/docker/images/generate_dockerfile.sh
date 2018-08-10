#!/usr/bin/env bash

mkdir bin/docker/images/$1

# Generate DockerFile
cp -f bin/docker/images/default/Dockerfile bin/docker/images/$1/Dockerfile
cp -f bin/docker/images/default/entrypoint.sh bin/docker/images/$1/entrypoint.sh

# Change the clause FROM
sed -i -e "s/FROM hipay\/hipay-magento2:2.1.10/FROM hipay\/hipay-magento2:$1/" bin/docker/images/$1/Dockerfile

sed -i -e "s/images\/default/images\/$1/" bin/docker/images/$1/Dockerfile

# Generate docker_compose
cp -f docker-compose.test.yml docker-compose.test-$1.yml

sed -i -e "s/images\/default/images\/$1/" docker-compose.test-$1.yml
sed -i -e "s/\${DOCKER_STACK}-\${DOCKER_SERVICE}_web:\${CI_COMMIT_REF_SLUG}/\${DOCKER_STACK}-\${DOCKER_SERVICE}_web-$1:\${CI_COMMIT_REF_SLUG}/" docker-compose.test-$1.yml
