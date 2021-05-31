#!/usr/bin/env bash
set -eo pipefail

# init environment variables
set -o allexport
{
  ROOT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
  COMPOSE="docker-compose"
  RUNNING=$(${COMPOSE} ps -q)
  [ "${RUNNING}" == "" ] && DO="run --rm" || DO="exec"
  PHP="php"
  if [ $# -gt 0 ]; then
    if [ "$1" == "php70" ] || [ "$1" == "php71" ] || [ "$1" == "php72" ]; then
      PHP="$1"
      shift 1
    fi
  fi
}
set +o allexport

# run commands
if [ $# -gt 0 ]; then
  if [ -f "bin/mp/$1" ]; then
    SCRIPT="$1"
    shift 1
    ${ROOT_DIR}/bin/mp/${SCRIPT} "$@"
  else
    ${COMPOSE} "$@"
  fi
else
  ${COMPOSE} ps
fi
