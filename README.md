# Simple PHP script to handle CSP reports with syslog

This simple php script will accept violation reports for your Content-Security-Policy and hand them over to syslog. This allows you to manage the data in any way you want, without the need for a complex CSP-report script.

Another benefit is that reports are not stored in a file withing the web directory, where they could potentially access by unauthorized parties (depending on your webserver configuration).

## Installation

Simply download the PHP script and put it in your web directory (e.g. `/var/www/yourapp/csp.php`). Then add `report-uri /csp.php;` to the end of your CSP header.

```
<?php
    # collect data from post request
    $data = file_get_contents('php://input');

    # set options for syslog daemon
    openlog('cspreport', LOG_NDELAY, LOG_USER);

    # send warning about csp report
    syslog(LOG_WARNING, $data);
?>
```

### Setting up syslog

On Ubuntu 16.04 server, rsyslog is installed by default. Simply create a new config file with the following content at `/etc/rsyslog.d/01-csp.conf`.

```
# Set APPLICATION.SEVERITY and define where the log entry should be written to
:syslogtag, isequal, "cspreport:" {
  :msg, contains, "csp-report" {
    *.*     /var/log/csp/csp.log                                # to local file
    #*.*    @graylog.lan:514;RSYSLOG_SyslogProtocol23Format     # to server (syslog, graylog, etc.)
  }
  stop
}
```

Afterwards, run `sudo service rsyslog restart` and your CSP violations should be sent to /var/log/csp.log.

### Configuring logrotate

In order to avoid the `csp.log` file filling your entire disk with violations, configure a log rotation schedule like the following in `/etc/logrotate.d/cspreport`.

```
/var/log/csp/*.log {
  rotate 12
  monthly
  compress
  missingok
  notifempty
}
```

Afterwards, run `sudo logrotate /etc/logrotate.d/cspreport` to make sure there are no errors in your logrotate config.
