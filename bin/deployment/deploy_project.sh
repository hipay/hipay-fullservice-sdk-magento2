port=$(wget --no-check-certificate --user=$DOCKER_MACHINE_LOGIN --password=$DOCKER_MACHINE_PASS -qO- https://docker-knock-auth.hipay.org/KyP54YzX/?srvname=deploy.hipay-pos-platform.com)

GITHUB_BRANCH=$CIRCLE_BRANCH
if [ $CIRCLE_TAG != "" ];then
    GITHUB_BRANCH=$CIRCLE_TAG
fi

echo "Deploy project for project $CIRCLE_PROJECT_REPONAME and branch $GITHUB_BRANCH"
sshpass -p $PASS_DEPLOY ssh root@docker-knock-auth.hipay.org -p $port  "export DOCKER_API_VERSION=1.23 && docker exec " \
    "deploy.hipay-pos-platform.com" /deploy/deploy_project.sh  $CIRCLE_PROJECT_REPONAME $GITHUB_BRANCH $CIRCLE_BUILD_URL gitlab
