#!/bin/bash


bakred='\033[41m'
bldred='\033[1;31m'
bldylw='\033[1;33m'
txtgrn='\033[0;32m'
txtylw='\033[0;33m'
txtrst='\033[0m'


lint() {
    local line_number=0
    local error_count=0
    while read path
    do
        line_number=$[$line_number +1]
        if [ "$line_number" -eq 1 ]; then
            echo -en "${txtgrn}Begin PHP Linter${txtrst}\n"
        fi
        echo -en "  ${txtylw}Checking ${bldylw}${path}${txtylw}...${txtrst} "
        php -l "$path" 1> /dev/null
        if [ $? -ne 0 ]; then
            error_count=$[$error_count +1]
            echo -en "${bldred}Error(s).${txtrst}\n"
        else
            echo -en "${txtylw}Ok.${txtrst}\n"
        fi
    done
    if [ "$error_count" -gt 0 ]; then
        echo -en "${bakred}$error_count PHP Parse error(s) were found! Aborting commit.${txtrst}\n"
        exit 1
    fi
    exit 0
}

git status --porcelain | grep -e '^\s*[AM]\(.*\).php$' | cut -c 3- | lint

exit $?
