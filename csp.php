<?php
    # collect data from post request
    $data = file_get_contents('php://input');

    # set options for syslog daemon
    openlog('cspreport', LOG_NDELAY, LOG_USER);

    # send warning about csp report
    syslog(LOG_WARNING, $data);
?>
