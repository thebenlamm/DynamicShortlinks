#!/bin/bash

cd ..
cp -r dynamic-shortlinks /tmp/dynamic-shortlinks
cd /tmp
rm -rf dynamic-shortlinks/.git
rm dynamic-shortlinks/deploy.sh
tar -czf dynamic-shortlinks.tar.gz dynamic-shortlinks
scp -i ~/Documents/certs/friedlam-websites.pem dynamic-shortlinks.tar.gz ubuntu@ec2-54-161-229-130.compute-1.amazonaws.com:~/plugins/dynamic-shortlinks.tar.gz
rm -rf dynamic-shortlinks
rm -rf dynamic-shortlinks.tar.gz