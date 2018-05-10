# Simple PHP script to handle CSP reports with syslog

This simple php script will accept violation reports for your Content-Security-Policy and hand them over to syslog. This allows you to manage the data in any way you want, without the need for a complex CSP-report script.

Another benefit is that reports are not stored in a file withing the web directory, where they could potentially access by unauthorized parties (depending on your webserver configuration).

## Installation

Simply download the PHP script and put it in your web directory. Then add `report-uri /csp.php;` to the end of your CSP header.

### Setting up syslog

On Ubuntu 16.04 server, rsyslog is installed by default. Simply create a new config file with the following content at `/etc/rsyslog.d/30-csp.conf`.

```
# Set APPLICATION.SEVERITY and define where the log entry should be written to
cspreport.warn                      /var/log/apache2/csp.log

# And if you want to forward it to a server like graylog for analysis as well
#cspreport.warn          @graylog.lan:514;RSYSLOG_SyslogProtocol23Format
```

Afterwards, run `sudo service rsyslog restart` and your CSP violations should be sent to /var/log/apache2/csp.log.

### Not using Apache2?

If you're not using Apache as a webserver, there are a couple of changes you should do.

  * replace the logfile location in your rsyslog config with one that's not located in the apache2 log folder
  * create a logrotate config for your logfile

### Configuring logrotate

If you're using Apache2, the logfile is caught automatically by logrotate, as it falls under the rotation rule matching `/var/log/apache2/*.log`. If you need to setup your own rotation schedule for files (to avoid csp report files filling your disk space), use the apache template in `/etc/logrotate.d/apache2` as a basis and go from there.

```
/var/log/apache2/*.log {
        daily
        missingok
        rotate 14
        compress
        delaycompress
        notifempty
        create 640 root adm
        sharedscripts
        postrotate
                if /etc/init.d/apache2 status > /dev/null ; then \
                    /etc/init.d/apache2 reload > /dev/null; \
                fi;
        endscript
        prerotate
                if [ -d /etc/logrotate.d/httpd-prerotate ]; then \
                        run-parts /etc/logrotate.d/httpd-prerotate; \
                fi; \
        endscript
}
```
