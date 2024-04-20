#!/bin/bash
#DO_NOT_RESTART_SERVICES=1
#ENABLE_STDOUT=1
#ENABLE_STDOUT_ONLY=1
################################################################################
# Configuration area
APP_TITLE='pd@min'
MAIL_RECEIVER=postmaster@localhost
CFG_FILE="$(dirname $0)/config/$(basename $0 | cut -d'.' -f1).cfg"
#
################################################################################

################################################################################
# DO NOT TOUCH THE LINES BELOW:
# will be overwritten in later steps
LOG_FILE="/var/log/pdadmin/pdadmin_$(date +'%Y-%m-%d').log"
#
function get
{
    echo "$(grep "^[[:space:]]*$1[[:space:]]*=" ${CFG_FILE} | sed s/.*=[[:space:]]*//)"
}
function usage
{
    ENABLE_STDOUT_ONLY=1
    {
    echo "" \
    && echo "ERROR: $*" \
    && echo "" \
    && echo "[usage]" \
    && echo "" \
    && echo "$(basename $0) [list-config]" \
    && echo ""
    } 2>&1 | log "[postfix] "
    exit 1;
}
function handlePostfix
{
    CURRENT_CFG_FILE=$(basename ${1})
    {  cmd "rm -f ${1}.db" \
    && cmd "${POSTMAP_BIN} ${1}" \
    && cmd "cp -f ${1} ${BACKUP_DIR}/$(basename ${1})" \
    && cmd "cp ${1} ${2}" \
    && cmd "mv ${1}.db ${2}" \
    && cmd "chmod ${POSTFIX_CFG_RIGHTS} ${2}/${CURRENT_CFG_FILE} ${2}/${CURRENT_CFG_FILE}.db" \
    && cmd "chown ${POSTFIX_CFG_OWNER} ${2}/${CURRENT_CFG_FILE} ${2}/${CURRENT_CFG_FILE}.db"
    } 2>&1 | log "[postfix] "
    return $?
}
function handleDovecot
{
    CURRENT_CFG_FILE=$(basename ${1})
    {  cmd "mkdir -p ${BACKUP_DIR}" \
    && cmd "cp -f ${1} ${BACKUP_DIR}/$(basename ${1})" \
    && cmd "cp ${1} ${2}" \
    && cmd "chmod ${DOVECOT_CFG_RIGHTS} ${2}/${CURRENT_CFG_FILE}" \
    && cmd "chown ${DOVECOT_CFG_OWNER} ${2}/${CURRENT_CFG_FILE}"
    } 2>&1 | log "[dovecot] "
    return $?
}
function handleAccounts
{
    RC=0
    while read line; do
        echo "${line}" | grep -v '^#' >/dev/null || continue
        account=$(echo "${line}" | tr -s ' ' | awk {'print $2'})
        if [ ! -z "${account}" ]; then
            if [ ! -d "${ACCOUNTS_DIR}/${account}" ]; then
                {  cmd "mkdir -p ${ACCOUNTS_DIR}/${account}" \
                && cmd "chmod ${ACCOUNT_DIR_RIGHTS} ${ACCOUNTS_DIR}/${account}" \
                && cmd "chown ${ACCOUNT_DIR_OWNER} ${ACCOUNTS_DIR}/${account}"
                } 2>&1 | log "[account] "
                RC=$?
                if [ ${RC} -ne 0 ]; then
                    break;
                fi
            else
                echo "The target directory already exists account '${account}'." | log "[account] "
            fi
        else
            echo "Could not find any account information in line: ${line}" | log "[account] "
        fi
    done < "${1}"
    if [ ${RC} -eq 0 ]; then
        for account in $(ls -1 ${ACCOUNTS_DIR}); do
            grep -q "${account}" "${1}" || echo "The ${account} is not active anymore" | log "[account] "
        done
    else
        echo "ERROR while updating accounts." | log "[account] "
    fi
    return ${RC}
}
function restartPostfix
{
    if [ ! -z "${DO_NOT_RESTART_SERVICES}" ]; then
        tput setab 1
        echo "!!! ATTENTION - Postfix will not be restartet, because of DO_NOT_RESTART_SERVICES=${DO_NOT_RESTART_SERVICES} !!!$(tput sgr0)" | log "[dovecot] " 
        return 0;
    fi
    cmd "${POSTFIX_RESTART}" 2>&1 | log "[postfix] "
    return $?
}
function restartDovecot
{
    if [ ! -z "${DO_NOT_RESTART_SERVICES}" ]; then
        tput setab 1
        echo "!!! ATTENTION - Dovecot will not be restartet, because of DO_NOT_RESTART_SERVICES=${DO_NOT_RESTART_SERVICES} !!!$(tput sgr0)" | log "[dovecot] " 
        return 0;
    fi
    cmd "${DOVECOT_RESTART}" 2>&1 | log "[dovecot] "
    return $?
}
function markSuccess
{
    {  cmd "mv ${1} ${2}" \
    && cmd "rm -f ${RELEASE_DIR}/*"
    } 2>&1 | log "[success] "
    return $?
}
function sendMail
{
    if [ ! -x "${SENDMAIL}" ] || [ -z "${MAIL_RECEIVER}" ]; then
        return 0
    fi
    SUBJECT=
    if [ $* -ne 0 ]; then
        SUBJECT="${APP_TITLE}: ERROR - Configuration-rollout failed"
    else
        SUBJECT="${APP_TITLE}: Configuration-rollout was successfull"
    fi
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
${SENDMAIL} ${MAIL_RECEIVER} <<EOF
subject:${SUBJECT}
from:"pd@min" <${MAIL_RECEIVER}>
Dear Postfix-administrator,

You receive this e-mail, because of a change in the server configuration.
This was done in the webfrontend of ${APP_TITLE} and was finally rolled
out by a scheduled script (with a delay of ${DEPLOY_INTERVAL} minutes).

Feel free to log into the webfrontend to check last rolled out configuration.

Best regards
Your pd@min ;-)
EOF
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    RC=$?
    rm ${MAIL_BODY}
    return ${RC}
}
function cmd
{
    echo "$> $*"
    $*

    # We do it this way, because setting a global variable for the returncode
    # or piping it to this function never worked. In the log-function we grep
    # for text with digits only.
    CMDRC=$?
    echo $CMDRC
    return $CMDRC
}
function log
{
    PREFIX=$*
    # read from stdin:
    while read IN; do
        
        # We do it this way, because setting a global variable for the returncode
        # or piping it to this function never worked. In the cmd-function we echo
        # the return code in a single line, so we can grep for digits at this
        # place
        if [ ! -z "$(echo "${IN}" | grep -E '^[0-9]+$')" ]; then
            if [ "${IN}" != "0" ]; then
                MSG="$(date +'%Y-%m-%d %H:%M:%S') ${PREFIX} Breaking up, because of an exitstatus = ${IN}";
                exit ${IN};
            else
                continue
            fi
        fi
        MSG="$(date +'%Y-%m-%d %H:%M:%S') ${PREFIX} $IN"
        if [ ! -z "${ENABLE_STDOUT}" ] || [ ! -z "${ENABLE_STDOUT_ONLY}" ]; then
            echo $MSG
        fi
        if [ -z "${ENABLE_STDOUT_ONLY}" ]; then
            echo $MSG >> ${LOG_FILE}
        fi
    done
    return ${RC}
}

if [ ! -f "${CFG_FILE}" ]; then
    usage "Configuration file '${CFG_FILE}' not found!"
fi
SENDMAIL=$(get 'SENDMAIL')
MAIL_RECEIVER=$(get 'MAIL_RECEIVER')
POSTMAP_BIN=$(get 'POSTMAP_BIN')
POSTFIX_CONF_DIR=$(get 'POSTFIX_CONF_DIR')
DOVECOT_CONF_DIR=$(get 'DOVECOT_CONF_DIR')
ACCOUNTS_DIR=$(get 'ACCOUNTS_DIR')
RELEASE_DIR=$(get 'RELEASE_DIR')
BACKUP_DIR=$(get 'BACKUP_DIR')
DEPLOY_INTERVAL=$(get 'DEPLOY_INTERVAL')
DEPLOY_NEXT_RUN=$(get 'DEPLOY_NEXT_RUN')
POSTFIX_RESTART=$(get 'POSTFIX_RESTART')
DOVECOT_RESTART=$(get 'DOVECOT_RESTART')
POSTFIX_CFG_OWNER=$(get 'POSTFIX_CFG_OWNER')
POSTFIX_CFG_RIGHTS=$(get 'POSTFIX_CFG_RIGHTS')
DOVECOT_CFG_OWNER=$(get 'DOVECOT_CFG_OWNER')
DOVECOT_CFG_RIGHTS=$(get 'DOVECOT_CFG_RIGHTS')
ACCOUNT_DIR_OWNER=$(get 'ACCOUNT_DIR_OWNER')
ACCOUNT_DIR_RIGHTS=$(get 'ACCOUNT_DIR_RIGHTS')
if [[ ! -x "${POSTMAP_BIN}" ]]; then
    usage "Can not execute '${POSTMAP_BIN}' (value of POSTMAP_BIN in '${CFG_FILE}')!"
elif [ -z "${POSTFIX_RESTART}" ]; then
    usage "Please set the value of POSTFIX_RESTART (in '${CFG_FILE}' to a valid postfix restart command!"
elif [ -z "${DOVECOT_RESTART}" ]; then
    usage "Please set the value of DOVECOT_RESTART (in '${CFG_FILE}' to a valid dovecot restart command!"
elif [ ! -d "${POSTFIX_CONF_DIR}" ]; then
    usage "No such a folder '${POSTFIX_CONF_DIR}' (value of POSTFIX_CONF_DIR in '${CFG_FILE}')!"
elif [ ! -d "${DOVECOT_CONF_DIR}" ]; then
    usage "No such a folder '${DOVECOT_CONF_DIR}' (value of DOVECOT_CONF_DIR in '${CFG_FILE}')!"
elif [ ! -d "${ACCOUNTS_DIR}" ]; then
    usage "No such a folder '${ACCOUNTS_DIR}' (value of ACCOUNTS_DIR in '${CFG_FILE}')!"
elif [ ! -d "${RELEASE_DIR}" ]; then
    usage "No such a folder '${RELEASE_DIR}' (value of RELEASE_DIR in '${CFG_FILE}')!"
elif [ ! -d "${BACKUP_DIR}" ]; then
    usage "No such a folder '${BACKUP_DIR}' (value of BACKUP_DIR in '${CFG_FILE}')!"
elif [[ "${DEPLOY_INTERVAL}" =~ '^[0-9]+$' ]]; then
    usage "Value '${DEPLOY_INTERVAL}' is not a number (value of DEPLOY_INTERVAL in '${CFG_FILE}')!"
elif [ -z "${DEPLOY_NEXT_RUN}" ]; then
    usage "Value '${DEPLOY_NEXT_RUN}' is not a number (value of DEPLOY_NEXT_RUN in '${CFG_FILE}')!"
elif [ -z "${POSTFIX_CFG_OWNER}" ]; then
    usage "Value '${POSTFIX_CFG_OWNER}' is not a number (value of POSTFIX_CFG_OWNER in '${CFG_FILE}')!"
elif [ -z "${POSTFIX_CFG_RIGHTS}" ]; then
    usage "Value '${POSTFIX_CFG_RIGHTS}' is not a number (value of POSTFIX_CFG_RIGHTS in '${CFG_FILE}')!"
elif [ -z "${DOVECOT_CFG_OWNER}" ]; then
    usage "Value '${DOVECOT_CFG_OWNER}' is not a number (value of DOVECOT_CFG_OWNER in '${CFG_FILE}')!"
elif [ -z "${DOVECOT_CFG_RIGHTS}" ]; then
    usage "Value '${DOVECOT_CFG_RIGHTS}' is not a number (value of DOVECOT_CFG_RIGHTS in '${CFG_FILE}')!"
elif [ -z "${ACCOUNT_DIR_OWNER}" ]; then
    usage "Value '${ACCOUNT_DIR_OWNER}' is not a number (value of ACCOUNT_DIR_OWNER in '${CFG_FILE}')!"
elif [ -z "${ACCOUNT_DIR_RIGHTS}" ]; then
    usage "Value '${ACCOUNT_DIR_RIGHTS}' is not a number (value of ACCOUNT_DIR_RIGHTS in '${CFG_FILE}')!"
fi

if [ $# -eq 1 ]; then
    if [ "$*" == "list-config" ]; then
        echo ""
        echo "The configuration of this setup was found in ${CFG_FILE}:"
        echo ""
        echo "POSTMAP_BIN .......... ${POSTMAP_BIN}"
        echo "POSTFIX_CONF_DIR ..... ${POSTFIX_CONF_DIR}"
        echo "DOVECOT_CONF_DIR ..... ${DOVECOT_CONF_DIR}"
        echo "ACCOUNTS_DIR ......... ${ACCOUNTS_DIR}"
        echo "RELEASE_DIR .......... ${RELEASE_DIR}"
        echo "BACKUP_DIR ........... ${BACKUP_DIR}"
        echo "DEPLOY_INTERVAL ...... ${DEPLOY_INTERVAL}"
        echo "DEPLOY_NEXT_RUN ...... ${DEPLOY_NEXT_RUN}"
        echo "POSTFIX_RESTART ...... ${POSTFIX_RESTART}"
        echo "DOVECOT_RESTART ...... ${DOVECOT_RESTART}"
        echo "SENDMAIL ............. ${SENDMAIL}"
        echo "MAIL_RECEIVER ........ ${MAIL_RECEIVER}"
        echo "POSTFIX_CFG_OWNER .... ${POSTFIX_CFG_OWNER}"
        echo "POSTFIX_CFG_RIGHTS ... ${POSTFIX_CFG_RIGHTS}"
        echo "DOVECOT_CFG_OWNER .... ${DOVECOT_CFG_OWNER}"
        echo "DOVECOT_CFG_RIGHTS ... ${DOVECOT_CFG_RIGHTS}"
        echo "ACCOUNT_DIR_OWNER .... ${ACCOUNT_DIR_OWNER}"
        echo "ACCOUNT_DIR_RIGHTS ... ${ACCOUNT_DIR_RIGHTS}"
        echo ""
        exit 1
    else
        usage "Unknown commandline parameter '$*'!"
    fi
elif [ $# -gt 1 ]; then
    usage "Unsupported parameters '$*'!"
fi
if [ -z "${ENABLE_STDOUT}" ]; then
    echo "This script sends its output to ${LOG_FILE}"
    echo "This script sends its output to ${LOG_FILE}" >> ${LOG_FILE}
fi

LOG_FILE="/var/log/pdadmin/pdadmin_$(date +'%Y-%m-%d').log"

while true; do
    count=$(ls -1 ${RELEASE_DIR}/{account_addresses,accounts,address_aliases,domains,passwords} 2>/dev/null | wc -l)
    passwordOnly=$(ls -1 ${RELEASE_DIR}/passwords 2>/dev/null | wc -l)
    
    #if [ ${count} -eq 4 ] || [ ${count} -gt 0 -a ${count} -eq ${passwordOnly} ]; then
    if [ ${count} -eq 5 ] || [ ${count} -gt 0 -a ${count} -eq ${passwordOnly} ]; then
        
        DATE=$(date +'%Y%m%d')
        TIME=$(date +'%H%M%S')
        BACKUP_TARGET=${BACKUP_DIR}/${DATE}_${TIME}
        SETUP_OK=${BACKUP_TARGET}_ok

        if [ ! -z "${DO_NOT_RESTART_SERVICES}" ]; then
            tput setab 1
            {  echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!" \
            && echo "!!! ATTENTION !!! Dovecot will not be restartet, because of an enabled variable 'DO_NOT_RESTART_SERVICES' !!! ATTENTION !!!" \
            && echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!$(tput sgr0)"
            } | log "[M-A-I-N] "
            tput sgr0
        fi

        cmd "mkdir -p ${BACKUP_TARGET}" 2>&1 | log "[M-A-I-N] "

        if [ ${count} -eq ${passwordOnly} ]; then
            SETUP_OK=${BACKUP_TARGET}_pw_only
            handleDovecot  "${RELEASE_DIR}/passwords" ${DOVECOT_CONF_DIR} && \
            restartDovecot                                                && \
            markSuccess ${BACKUP_TARGET} ${SETUP_OK}
        else
            handlePostfix  "${RELEASE_DIR}/domains" ${POSTFIX_CONF_DIR}   && \
            handlePostfix  "${RELEASE_DIR}/accounts" ${POSTFIX_CONF_DIR}  && \
            handlePostfix  "${RELEASE_DIR}/account_addresses" ${POSTFIX_CONF_DIR} && \
            handlePostfix  "${RELEASE_DIR}/address_aliases" ${POSTFIX_CONF_DIR}   && \
            restartPostfix                                                && \
            handleDovecot  "${RELEASE_DIR}/passwords" ${DOVECOT_CONF_DIR} && \
            handleAccounts "${RELEASE_DIR}/accounts"                      && \
            restartDovecot                                                && \
            cmd "cp -f ${RELEASE_DIR}/tests ${BACKUP_TARGET}" 2>&1 | log "[M-A-I-N] " && \
            markSuccess ${BACKUP_TARGET} ${SETUP_OK}
            sendMail $?
        fi

        echo "Waiting for ${DEPLOY_INTERVAL} minutes until the next check." | log "[M-A-I-N] "
        if [ -d ${SETUP_OK} ]; then
            mv ${LOG_FILE} ${SETUP_OK}
        else
            mv ${LOG_FILE} ${BACKUP_TARGET}
        fi
        LOG_FILE="/var/log/pdadmin/pdadmin_$(date +'%Y-%m-%d').log"
    elif [ ${count} -ne 0 ]; then
        echo "There is an incomplete set of files (${count}/5) in the release directory '${RELEASE_DIR}'." | log "[M-A-I-N] "
    fi

    NEXT_TIME=$(date -d "${DEPLOY_INTERVAL} minutes" +'%Y-%m-%d %H:%M:%S')
    echo "${DEPLOY_INTERVAL} ${NEXT_TIME}" > ${DEPLOY_NEXT_RUN}
    for (( i = ${DEPLOY_INTERVAL}; i >= 0; i-- )); do
        for j in {60..0}; do
            (( countdown = i * 60 + j ))
            echo "${countdown} ${NEXT_TIME}" > ${DEPLOY_NEXT_RUN}
            sleep 1s
        done
    done
done

