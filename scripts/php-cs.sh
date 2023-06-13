#!/bin/bash


bakred='\033[41m'
bldred='\033[1;31m'
bldylw='\033[1;33m'
txtgrn='\033[0;32m'
txtylw='\033[0;33m'
txtrst='\033[0m'


codesniffer() {
    local paths=`cat`
    local directory="$(git rev-parse --show-toplevel)"
    if [ ! -z "$paths" ]; then
        echo -en "${txtgrn}Begin PHP Codesniffer${txtrst}\n"
        "${directory}/vendor/bin/phpcs" -q $paths
        if [ $? -ne 0 ]; then
            echo -en "${bakred}Error(s) detected by PHP CodeSniffer! Aborting commit.${txtrst}\n"
            exit 1
        fi
    fi
    #exit 0
}

git status --porcelain | grep -e '^\s*[AM]\(.*\).php$' | cut -c 3- | codesniffer

exit $?
