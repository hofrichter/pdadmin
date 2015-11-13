
In diesem Tutorial wurde "mail.domain.tld" als Platzhalter in Befehlen und in
den Templates als Domain des Mailservers verwendet.

================================================================================
[1] https://skrilnetz.net/setup-your-own-mailserver/
[2] https://arnowelzel.de/wp/greylisting-zur-spamvermeidung
[3] https://thomas-leister.de/open-source/linux/ubuntu/postfix-amavis-spamfilter-spamassassin-sieve/
[4] https://www.df.eu/de/service/df-faq/cloudserver/anleitungen/spam-und-virenschutz-mit-postfix-debian/
[5] http://www.postfix.org/addon.html

Postfix:      [1], [2], [3], [4], [5]
Dovecot:      [1],      [3]
Spamassassin:           [3], [4]
Amavis:                 [3], [4]
DKIM:         [1]
================================================================================



================================================================================
=                      POSTFIX und DOVECOT einrichten                          =
================================================================================
= Dieser Abschnitt der Installations- und Konfigurationsanleitung basiert auf  =
= [1] wurde aber auf den Support virtueller Mail-Accounts, -Domains und        =
= -Adressen umgestellt. Der aus [1] resultierenden Fehler wurde mit Hilfe von  =
= [2] gelöst.                                                                  = 
================================================================================

1.  System vorbereiten:
    1.a Maildomain anlegen
        echo domain.tld > /etc/mailname

    1.b Technischen User anlegen:
        groupadd -g 5000 vmail \
            || useradd -s /usr/sbin/nologin -u 5000 -g 5000 vmail \
            || usermod -aG vmail postfix \
            || usermod -aG vmail dovecot

2.  Postfix, Dovecot und weitere nette Server(komponenten) installieren
    apt-get install postfix postfix-policyd-spf-perl postgrey dovecot-core dovecot-imapd opendkim opendkim-tools && service postfix stop && service dovecot stop
    
3.  RSA-Key erstellen
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/mail.domain.tld.key -out /etc/ssl/certs/mail.domain.tld.pem

4.  Backup erstellen
    cp /etc/postfix/master.cf /etc/postfix/master.cf_orig
    cp /etc/postfix/main.cf /etc/postfix/main.cf_orig
    cp /etc/dovecot/dovecot.conf /etc/dovecot/dovecot.conf_orig
 
5.  Anpassen der Datei "/etc/postfix/main.cf"
    5.a Inhalt mit dem aus dem Template ersetzen
    5.b Wert von "myhostname" durch den Wert aus "/etc/hostname"
    5.c Platzhalter durch reale Angaben ersetzen:
        ~ domain.tld
        ~ netcup-subdomain.yourvserver.net (siehe "main.cf_orig")
 
6.  Anpassen der Datei "/etc/postfix/master.cf"
    6.a Inhalt mit dem aus dem Template ersetzen (keine Anpassungen erforderlich)

7.  Anpassen der Datei "/etc/dovecot/dovecot.conf"
    7.a Inhalt mit dem aus dem Template ersetzen
    7.b Anpassen des Pfades zu den SSL-Dateien
        ssl_cert = </etc/ssl/certs/mail.domain.tld.crt
        ssl_key = </etc/ssl/private/mail.domain.tld.key


8.  DKIM einrichten (es können auch die Templates verwendet werden und in ihnen der Platzhalter domain.tld ersetzt werden)
    8.a mkdir /etc/opendkim
    8.b echo "default._domainkey.mail.domain.tld mail.domain.tld:default:/etc/opendkim/default.private" > /etc/opendkim/KeyTable
    8.c echo "*@domain.tld default._domainkey.mail.domain.tld" > /etc/opendkim/SigningTable
    8.d opendkim-genkey -s default -d mail.domain.tld -D /etc/opendkim
    8.e chown opendkim:opendkim /etc/opendkim/default.private
    8.f echo 'SOCKET="inet:8891@localhost"' >> /etc/default/opendkim

    8.g Anpassen der Datei "/etc/opendkim.conf"
        8.g.i  Inhalt mit dem aus dem Template ersetzen
        8.g.ii "domain.tld" durch die des Mailservers ersetzen

9.  Postfächer konfigurieren
    9.a Domains festlegen: /etc/postfix/domains
        -> Schema: <domain.tld> OK
           WICHTIG: jede Zeile erfordert eine Domain UND ein abschließendes "OK"!
        

    9.b User definieren: /etc/postfix/accounts
        -> Schema für Maildir (Slash hinterm Usernamen): <email-address> <username>/
        -> Schema für MBox: <email-address> <username>

    9.c Mailaccount anlegen
        9.c.i   Für jeden User ein Mailverzeichnis anlegen:
                mkdir -p /var/mail/vhosts/<user>
        9.c.ii  Passwort generieren:
                doveadm pw -s SHA512 -u <username>
        9.c.iii Die generierte Zeile hier aufnehmen (pro Zeile ein Eintrag)
                echo "<username>:{SHA512}..." >> /etc/dovecot/passwords
   
    9.d Alias-Adressen anlegen: /etc/postfix/addresses
        -> Schema für spez. Addresse:      <address>@<domain.tld> <username>
        -> Schema für catchall-Addresse:   @<domain.tld>          <username>

    9.e Änderungen übersetzen 
        9.e.i   postmap /etc/postfix/domains /etc/postfix/accounts /etc/postfix/addresses
        -> Dieser Schritt ist IMMER erforderlich, wenn Änderungen in den Dateien
           vorgenommen wurden.
        

================================================================================
=              Amavis Spamfilter mit Spamassassin und Sieve                    =
================================================================================
= Dieser Abschnitt der Installations- und Konfigurationsanleitung basiert auf  =
= [3] und [4].                                                                 = 
================================================================================
=  Antispam und Spamfilter einrichten:


11. sudo apt-get install clamav clamav-daemon spamassassin spamc amavisd-new arj bzip2 cabextract cpio file gzip nomarch pax unzip zoo zip zoo
12. groupadd spamd
13. useradd -g spamd -s /bin/false -d /var/log/spamassassin spamd

14. mkdir /var/log/spamassassin
15. chown spamd:spamd /var/log/spamassassin
16. vi /etc/default/spamassassin
    16.1 Werte ändern/setzen (ist im Template enthalten):
         - ENABLED=1
         - CRON=1
         - OPTIONS="--create-prefs --max-children 2 --allow-tell --username spamd -H /var/log/spamassassin/ -s /var/log/spamassassin/spamd.log"
17. service spamassassin start

18. vi /etc/postfix/master.cf
    18.1 Prüfen, dass smtp-Zeile eine zusätzliche -o-Option enthält (ist im Template enthalten):
        smtp      inet  n       -       -       -       -       smtpd
          -o content_filter=spamassassin
    18.2 Prüfen, dass der content_filter definiert wurde (ist im Template enthalten):
         spamassassin unix -     n       n       -       -       pipe
                user=spamd argv=/usr/bin/spamc -f -e  
                /usr/sbin/sendmail -oi -f ${sender} ${recipient}


19. service postfix restart


20. vi /etc/spamassassin/local.cf (ist im Template enthalten):
    20.1 rewrite_header Subject [***** SPAM _SCORE_ *****]
    20.2 required_score 3.0
    20.3 use_bayes 1
    20.4 bayes_auto_learn 1

20.1 Optional kann man ein Skript noch zwischenschalten:
     https://wiki.apache.org/spamassassin/IntegratedSpamdInPostfix

21. service spamassassin restart

22. Spamservice testen:
    22.1 spamassassin -D -t < /usr/share/doc/spamassassin/examples/sample-spam.txt 2>&1 | tee sa.out

23. Amavis Konfiguration prüfen (ist im Template bereits enthalten):
    23.1 vi /etc/amavis/conf.d/15-content_filter_mode
         # diese Zeile aktivieren
         @bypass_virus_checks_maps = (
               \%bypass_virus_checks, \@bypass_virus_checks_acl, \$bypass_virus_checks_re);
         @bypass_spam_checks_maps = (
               \%bypass_spam_checks, \@bypass_spam_checks_acl, \$bypass_spam_checks_re);

    23.2 vi /etc/amavis/conf.d/50-user ("domain.tld" ist korrekt, bitte nicht ändern!)
         $hdrfrom_notify_sender = "amavisd-new <postmaster\@$myhostname>";


23b Gruppen gleichziehen, damit die einzelnen Server miteinander ohne
    "permission denied" reden können:
    usermod -a -G clamav amavis
    usermod -a -G clamav clamav
    usermod -a -G amavis clamav
    usermod -a -G amavis amavis

24 Services restarten
   service clamav-daemon restart
   service amavis restart
   
23.3 vi /etc/postfix/master.cf (ist im Template bereits enthalten)
   # diese Zeile sollte eine der letzten in der Datei sein:
   smtp-amavis  unix    -    -    n    -    2    smtp
         -o smtp_data_done_timeout=1200
         -o smtp_send_xforward_command=yes
         -o disable_dns_lookups=yes

23.4 vi /etc/postfix/main.cf (ist im Template bereits enthalten)
   # diese Zeile sollte eine der letzten in der Datei sein:
   content_filter = smtp-amavis:[127.0.0.1]:10024
   receive_override_options = no_address_mappings

23.5 vi /etc/postfix/master.cf (ist im Template bereits enthalten)

        127.0.0.1:10025 inet    n    -    n    -    -    smtpd
         -o content_filter=
         -o local_recipient_maps=
         -o relay_recipient_maps=
         -o smtpd_restriction_classes=
         -o smtpd_helo_restrictions=
         -o smtpd_sender_restrictions=
         -o smtpd_recipient_restrictions=permit_mynetworks,reject
         -o mynetworks=127.0.0.0/8
         -o strict_rfc821_envelopes=yes
         -o smtpd_error_sleep_time=0
         -o smtpd_soft_error_limit=1001
         -o smtpd_hard_error_limit=1000
         -o receive_override_options=no_header_body_checks

23.6 service postfix restart
23.7 telnet localhost 10025
23.8 service amavis restart

23.9 LOG-File /var/log/mail.log prüfen und nach "amavis"-Auschriften suchen
     grep -i 'amavis\[' /var/log/mail.log


24. sudo apt-get install razor pyzor


24.1 su - spamd
     razor-admin -home=/etc/spamassassin/.razor -create
     razor-admin -home=/etc/spamassassin/.razor -register
     razor-admin -home=/etc/spamassassin/.razor -discover

24.2 su - spamd
     pyzor discover


24.3 vi /etc/spamassassin/local.cf (ist im Template bereits enthalten)
     razor_config /etc/spamassassin/.razor/razor-agent.conf
     pyzor_options --homedir /etc/spamassassin

25  Spammails zum Lernen besorgen:
     mkdir -p /home/amavis; cd /home/amavis
     wget http://spamassassin.apache.org/publiccorpus/20021010_spam.tar.bz2
     tar xvf 20021010_spam.tar.bz2

25.1 Nun können Sie diese Nachrichten mit folgendem Befehl trainieren:
     su amavis -c 'sa-learn --spam /home/amavis/spam/'
# 25.2 Das Trainieren von Ham-Nachrichten ist mit folgendem Befehl möglich:
#      su amavis -c 'sa-learn --ham /home/amavis/ham/'

25.3 Wenn Sie empfangene E-Mails trainieren möchten, empfiehlt es sich, diese in
     Unterordner einzusortieren, damit nur die gewünschten E-Mails trainiert werden.
     Unterorder lassen sich per IMAP bequem anlegen. Auf dem Server lassen sich
     die E-Mails mit folgenden Befehlen für das Maildir-Format als Spam trainieren:
     su amavis -c 'sa-learn --spam /var/mail/<path-to>/.Junk/cur'

# 25.4 Für das Trainieren als Ham gilt folgender Befehl:
#      su amavis -c 'sa-learn --ham /var/mail/<path-to>/.Ham/cur'

25.5 Um die Regeln zu aktualisieren und die Datenbank neu einzulesen, wird der folgende Befehl benötigt:
     sa-update -D


25.4 sudo spamassassin restart

# ungeklärt ist noch dieser Fehler, der bei Aufruf restart geworfen wird
#> warn: archive-iterator: no access to restart: Datei oder Verzeichnis nicht gefunden at /usr/share/perl5/Mail/SpamAssassin/ArchiveIterator.pm line 830.
#> warn: archive-iterator: unable to open restart: Datei oder Verzeichnis nicht gefunden

Ab sofort kann die Spam-Erkennungsrate durch regelmäßige Anwendung verbessert werden (<user> ist der jeweilige Mailaccount):
sudo sa-learn --spam -u spamd --dir /var/mail/vhosts/<user>/.JUNK/* -D
sudo sa-learn --ham -u spamd --dir /var/mail/vhosts/<user>/.INBOX/* -D



================================================================================
=                            Mailserver testen                                 =
================================================================================

30. Mailserver testen:

    30.1 SMTP Testen:

         telnet: > telnet localhost smtp
         server: Trying 192.0.2.10...
         server: Connected to localhost.
         server: Escape character is '^]'.
         server: 220 mail.example.com ESMTP Postfix (Debian/GNU)
         client: EHLO localhost
         server: 250-mail.example.com
         server: 250-PIPELINING
         server: 250-SIZE 30720000
         server: 250-VRFY
         server: 250-ETRN
         server: 250-STARTTLS
         server: 250-AUTH LOGIN DIGEST-MD5 PLAIN CRAM-MD5
         server: 250-AUTH=LOGIN DIGEST-MD5 PLAIN CRAM-MD5
         server: 250-ENHANCEDSTATUSCODES
         server: 250-8BITMIME
         server: 250 DSN
         client: MAIL FROM:<test@example.com>
         server: 250 2.1.0 Ok
         client: RCPT TO:<user@example.com>
         server: 250 2.1.5 Ok
         client: DATA
         server: 354 End data with <CR><LF>.<CR><LF>
         client: Subject: Testnachricht
         client: 
         client: Das ist ein Test.
         client: 
         client: .
         server: 250 2.0.0 Ok: queued as 83398728027
         client: QUIT
         server: 221 2.0.0 Bye
         server: Connection closed by foreign host.
    
    
    30.2 IMAP Testen:
         telnet: > telnet localhost imap
         server: Trying 192.0.2.2...
         server: Connected to localhost.
         server: Escape character is '^]'.
         server: * OK Dovecot ready.
         client: a1 LOGIN <username> <password>
         server: a1 OK Logged in.
         client: a2 LIST "" "*"
         server: * LIST (\HasNoChildren) "." "INBOX"
         server: a2 OK List completed.
         client: a3 EXAMINE INBOX
         server: * FLAGS (\Answered \Flagged \Deleted \Seen \Draft)
         server: * OK [PERMANENTFLAGS ()] Read-only mailbox.
         server: * 1 EXISTS
         server: * 1 RECENT
         server: * OK [UNSEEN 1] First unseen.
         server: * OK [UIDVALIDITY 1257842737] UIDs valid
         server: * OK [UIDNEXT 2] Predicted next UID
         server: a3 OK [READ-ONLY] Select completed.
         client: a4 FETCH 1 BODY[]
         server: * 1 FETCH (BODY[] {405}
         server: Return-Path: sender@example.com
         server: Received: from client.example.com ([192.0.2.1])
         server:         by mx1.example.com with ESMTP
         server:         id <20040120203404.CCCC18555.mx1.example.com@client.example.com>
         server:         for <recipient@example.com>; Tue, 20 Jan 2004 22:34:24 +0200
         server: From: sender@example.com
         server: Subject: Test message
         server: To: recipient@example.com
         server: Message-Id: <20040120203404.CCCC18555.mx1.example.com@client.example.com>
         server: 
         server: This is a test message.
         server: )
         server: a4 OK Fetch completed.
         client: a5 LOGOUT
         server: * BYE Logging out
         server: a5 OK Logout completed.


================================================================================
=                            ZUSATZ-Informationen                              =
================================================================================

.:: Postgrey ::.

Postgrey erfordert keine zusätzliche Konfiguration. Greylisting verhindert Spam,
indem die Zustellung der Mail beim ersten Mal verhindert wird. Erst bei einem
erneuten Versuch landet die Mail beim Empfänger. Grund ist, dass Spamer i.d.R.
einen solchen zweiten Versuch aus Lastgründen nicht vornehmen und somit nur
"ehrliche" Mailserver durchgelassen werden.


.:: clamav ::.
ClamAV ist für amavis erforderlich, muss allerdings nicht konfiguriert oder
irgendwo mit anderen Programmen verknüpft werden.

