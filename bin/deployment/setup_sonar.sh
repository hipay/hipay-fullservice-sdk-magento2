echo $CI_PULL_REQUESTS
echo $CI_PULL_REQUEST
echo $CIRCLE_PR_NUMBER
sudo apt-get install unzip
sudo wget https://sonarsource.bintray.com/Distribution/sonar-scanner-cli/sonar-scanner-cli-3.0.3.778-linux.zip
sudo unzip sonar-scanner-cli-3.0.3.778-linux.zip
sudo mv sonar-scanner-3.0.3.778-linux /home/ubuntu/bin/
sudo chmod -R a+x /home/ubuntu/bin/sonar-scanner-3.0.3.778-linux/
sudo ln -s --force /home/ubuntu/bin/sonar-scanner-3.0.3.778-linux/bin/sonar-scanner /usr/local/bin/sonar-scanner
