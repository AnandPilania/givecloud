#!/bin/bash


bakred='\033[41m'
bldred='\033[1;31m'
bldylw='\033[1;33m'
txtgrn='\033[0;32m'
txtylw='\033[0;33m'
txtrst='\033[0m'


dump_check() {
    local line_number=0
    local error_count=0
    while read path
    do
        line_number=$[$line_number +1]
        if [ "$line_number" -eq 1 ]; then
            echo -en "${txtgrn}Begin Dump Checker (i.e. dd, var_dump, etc...)${txtrst}\n"
        fi
        echo -en "  ${txtylw}Checking ${bldylw}${path}${txtylw}...${txtrst} "
        found=$(grep -E "(^|;|{|}|\(|\)|\s)(dd|var_dump)\(" "$path")
        if [ ! -z "$found" ]; then
            error_count=$[$error_count +1]
            echo -en "${bldred}Found.${txtrst}\n"
        else
            echo -en "${txtylw}Ok.${txtrst}\n"
        fi
    done
    if [ "$error_count" -gt 0 ]; then
        echo -en "${bakred}$error_count dump statement(s) found! Aborting commit.${txtrst}\n"
        exit 1
    fi
    exit 0
}

git status --porcelain | grep -e '^\s*[AM]\(.*\).php$' | cut -c 3- | dump_check

exit $?
