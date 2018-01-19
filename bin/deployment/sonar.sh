if [ -z "$CI_PULL_REQUESTS" ];then
    echo "Not a pull request"
elif [ $CI_PULL_REQUESTS != "" ];then
    PR_NUMBER=$(echo $CI_PULL_REQUEST | cut -d'/' -f 7)
    sonar-scanner -Dsonar.host.url=$SONAR_URL -Dsonar.login=$SONAR_LOGIN -Dsonar.analysis.mode=preview -Dsonar.github.pullRequest=$PR_NUMBER -Dsonar.github.oauth=$GITHUB_TOKEN
fi

if [ $CIRCLE_BRANCH = "develop" ];then
    sonar-scanner -Dsonar.host.url=$SONAR_URL -Dsonar.login=$SONAR_LOGIN
fi
