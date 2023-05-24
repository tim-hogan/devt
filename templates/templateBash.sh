#!/bin/bash

#*****************************************************************************************
# Current Directory
#*****************************************************************************************
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

#*****************************************************************************************
# Global defines
#*****************************************************************************************
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
YELLOW='\033[1;33m'
NC='\033[0m' 


#*****************************************************************************************
# Getting input options
#*****************************************************************************************
while getopts ":ab:" o; do
    case "${o}" in
        a)
            A=true
            ;;
        b)
            B=${OPTARG}
            ;;
        *)
            #Something else
            #call error
            ;;
    esac
done


#*****************************************************************************************
# BASH Functions
#*****************************************************************************************

#*****************************************************************************************
# getApacheEnv
# Description: Get an enviroment variable form a host copnfiguiration file in Apache
# Parameters: $1 the Site name without the .conf
#             $2 the envriob=nment variable name
# Returns     $? TYhe string of the varaibale found or empty string if not found
#*****************************************************************************************
function getAPacheEnv()
{
	#Pareameter $1 is the site name
	#Parameter  $2 is the envirnment varialbe
    local v=""
	if [ -f "/etc/apache2/sites-available/${1}.conf" ] ; then
        while read -r LINE 
        do
            if  [ "$(echo $LINE | awk '{print $1}')" == "SetEnv" ] && [ "$(echo $LINE | awk '{print $2}')" == ${2} ] ; then
                v=$(echo $LINE | awk '{print $3}')
            fi
        done < "/etc/apache2/sites-available/${1}.conf"
    fi 
    echo "$v"
}

#*****************************************************************************************
# Example use of getApacheEnv
#*****************************************************************************************
VARIABLE=$(getAPacheEnv hsm1.devt.nz PEPPER)

#*****************************************************************************************
# TESTS
#*****************************************************************************************

#check run as root
if [ "$EUID" -ne 0 ]; then
  echo -e "${RED}ERROR: Please run as root with sudo${NC}" >&2
  exit 1
fi

#file
if [ -f "<filename>"] ; then
fi

#directory
if [ -d "<directory name>"] ; then
fi

#test if variable is only numbers
REG="^[0-9]+$"
if [[ $V  =~ $REG ]] ; then
    echo "V Is number"
fi

#zero length string
if  [[ -z $var ]] ; then
    echo "Variable is zero length"
fi

//test string length
if [ $(#var) eq 10] ; then
    echo "${var} string length is eql 10"
fi
#string is eql value
if [ "$v" == "y" ] ; then
    echo "String is eql y"
fi

#string is not eql value
if [ "$v" != "y" ] ; then
    echo "String is not eql y"
fi

#check if a service is running
if systemctl is-active --quiet <service name> ; then
fi

#check if a service exsits
if systemctl status <service name> > /dev/null 2>&1  ; then
fi
