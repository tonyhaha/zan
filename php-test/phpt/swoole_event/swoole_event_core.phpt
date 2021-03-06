--TEST--
swoole_event: swoole_event_exit coredump

--SKIPIF--
<?php require  __DIR__ . "/../inc/skipif.inc"; ?>
--INI--
assert.active=1
assert.warning=1
assert.bail=0
assert.quiet_eval=0


--FILE--

<?php
function dnsLookup() {
    swoole_async_dns_lookup("www.youzan.com", function($host, $ip) {
        // echo posix_getpid(), ": $ip\n";
        swoole_event_exit();
        exit();
    });
}


$i = 200;
while (--$i) {
    $pid = pcntl_fork();
    if ($pid < 0) {
        exit;
    }

    if ($pid === 0) {
        dnsLookup();
        exit();
    }

    pcntl_waitpid($pid, $status);
    if (!pcntl_wifexited($status)) {
        fprintf(STDERR, "$pid %s exit [exit_status=%d, stop_sig=%d, term_sig=%d]\n",
            pcntl_wifexited($status) ? "normal": "abnormal",
            pcntl_wexitstatus($status),
            pcntl_wstopsig($status),
            pcntl_wtermsig($status)
        );
        exit(1);
    }
}
echo "SUCCESS";
?>

--EXPECT--
SUCCESS


