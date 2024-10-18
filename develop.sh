#!/usr/bin/env bash

#================================================================
# HEADER
#================================================================
#% SYNOPSIS
#+    [DOCKER_CONTAINER] /.${SCRIPT_NAME}
#%
#% DESCRIPTION
#%    This is a script to automate and simplify menial daily tasks.
#%
#% EXAMPLES
#%    DOCKER_CONTAINER=telemetry-service /.${SCRIPT_NAME}
#%./developi
#================================================================
#- IMPLEMENTATION
#-    version         ${SCRIPT_NAME} 0.0.1
#-    author          Ryan Grippeling <R.Grippeling@hotmail.nl>
#-    license         MIT
#-
#================================================================
#  HISTORY
#     2024-04-23 : Ryan Grippeling : Started this script
#
#================================================================
# END_OF_HEADER
#================================================================

SCRIPT_HEADSIZE=$(head -200 ${0} |grep -n "^# END_OF_HEADER" | cut -f1 -d:)
SCRIPT_NAME="$(basename ${0})"

usage() { printf "Usage: "; head -${SCRIPT_HEADSIZE:-99} ${0} | grep -e "^#+" | sed -e "s/^#+[ ]*//g" -e "s/\${SCRIPT_NAME}/${SCRIPT_NAME}/g" ; }
usagefull() { head -${SCRIPT_HEADSIZE:-99} ${0} | grep -e "^#[%+-]" | sed -e "s/^#[%+-]//g" -e "s/\${SCRIPT_NAME}/${SCRIPT_NAME}/g" ; }
scriptinfo() { head -${SCRIPT_HEADSIZE:-99} ${0} | grep -e "^#-" | sed -e "s/^#-//g" -e "s/\${SCRIPT_NAME}/${SCRIPT_NAME}/g"; }

checkVar () {
    if [ -z "$1" ]; then
        echo -n "You need to enter '$2': "
        exit 1
    fi
}

checkLastCommand () {
    if [ ! $? -eq 0 ]; then
        echo "Script failed."
        exit 1
    fi
}

export DOCKER_CONTAINER=${DOCKER_CONTAINER:-telemetry-service}

runOnDocker () {
    if [ ! $(docker inspect -f '{{.State.Running}}' $DOCKER_CONTAINER 2> /dev/null ) ]; then
        echo "Docker container \"$DOCKER_CONTAINER\" not running... Try \"docker-compose up -d $DOCKER_CONTAINER\"" > /dev/stderr
        exit 1
    fi

    docker exec -it $DOCKER_CONTAINER "$@"
}


# If we pass any arguments...
if [ $# -gt 0 ];then
    case "$1" in
        "info")
            scriptinfo
            ;;
        "help")
            usagefull
            ;;
        "enter")
            runOnDocker bash
            ;;
        "doctrine-migrations")
            shift
            runOnDocker php ./vendor/bin/doctrine-migrations "$@"
            ;;
        "console")
            shift
            runOnDocker php ./public/console.php  "$@"
            ;;
        "phpcs")
            shift
            docker run --rm -it -v $(pwd):/app -w /app php:8.3-cli php ./vendor/bin/phpcs --standard=phpcs.xml --extensions=php ./src
            ;;
        "phpcbf")
            shift
            docker run --rm -it -v $(pwd):/app -w /app php:8.3-cli php ./vendor/bin/phpcbf --standard=phpcs.xml --extensions=php ./src
            ;;
        "phpmd")
            shift
            docker run --rm -itv $(pwd):/app -w /app php:8.3-fpm php ./vendor/bin/phpmd src text phpmd.xml
            ;;
        "phpstan")
            shift
            docker run --rm -it -v $(pwd):/app -w /app php:8.3-fpm php ./vendor/bin/phpstan analyse src --level 8
            ;;
        "psalm")
            shift
            docker run --rm -itv $(pwd):/app -w /app php:8.3-fpm php ./vendor/bin/psalm --config=psalm.xml "$@"
            ;;
        "rector")
            shift
            docker run --rm -itv $(pwd):/app -w /app php:8.3-fpm php vendor/bin/rector process ./src
            ;;
        "composer-normalize")
            shift
            docker run --rm -itv $(pwd):/app -w /app composer:latest composer normalize
            ;;
        "test") # https://www.php.net/manual/en/intro.phpdbg.php -> TODO BETER DAN XDEBUG, gebruiken ipv php
            shift
            docker run --rm -itv $(pwd):/app -w /app php:8.3-fpm php vendor/phpunit/phpunit/phpunit -c phpunit.xml.dist --testsuite unit --fail-on-risky $@
            ;;
        "integration")
            shift
            runOnDocker vendor/phpunit/phpunit/phpunit -c phpunit.xml.dist --testsuite integration --fail-on-risky $@
            ;;
        "analyze")
            docker run --rm -itv $(pwd):/app -w /app php:8.3-fpm php vendor/bin/phpcs --standard=phpcs.xml --extensions=php --report=full pub
            ;;
        "pre-commit-phpcs")
            php vendor/bin/phpcs --standard=phpcs.xml pub
           ;;
        "pre-commit-static-analysis-changed")
            $(git --no-pager diff --name-only --diff-filter=MARC| grep -E 'pub/')
            if [ $? -eq 0 ]; then
                docker run --rm -v /$(pwd):/app phpstan/phpstan analyse --level max --no-progress $(git --no-pager diff --name-only --diff-filter=MARC| grep -E 'pub/')
            fi
           ;;
#        "jsinstall")
#            docker run --rm -v /${pwd:/app -w /app node:10-alpine npm install
#           ;;
#        "jsclean")
#            docker run --rm -v /${pwd:/app -w /app node:10-alpine sh -c "rm -rf public/js"
#          ;;
#        "jsbuild")
#            docker run --rm -v /${pwd:/app -w /app node:10-alpine sh -c "rm -rf public/js && npm run build"
#           ;;
#        "jswatch")
#            docker run --rm -v /${pwd:/app -w /app node:10-alpine npm run watch
#           ;;
#        "cy:open")
#            echo "Cypress is opening..."
#            node_modules/.bin/cypress open &
#           ;;
#        "cy:run")
#            node_modules/.bin/cypress run
#           ;;
        "log")
            runOnDocker tail -f /var/log/app.log
           ;;
        "composer")
            docker run --rm -itv $(pwd):/app -w /app composer "$@"
            ;;
        *)
        runOnDocker "$@"
        ;;
    esac
else
    usage
fi
