<?php 

// file with passwords
$pass = explode(PHP_EOL, file_get_contents('pass'));
// file with path with bitcoin wallet backup ex. ./bitcoin-wallet-backup-2017-08-06
$wallets = explode(PHP_EOL, file_get_contents('wallets'));

function liveExecuteCommand($cmd)
{

    while (@ ob_end_flush()); // end all output buffers if any

    $proc = popen("$cmd 2>&1 ; echo Exit status : $?", 'r');

    $live_output     = "";
    $complete_output = "";

    while (!feof($proc))
    {
        $live_output     = fread($proc, 4096);
        $complete_output = $complete_output . $live_output;
        echo "$live_output";
        @ flush();
    }

    pclose($proc);

    // get exit status
    preg_match('/[0-9]+$/', $complete_output, $matches);

    // return exit status and intended output
    return array (
        'exit_status'  => intval($matches[0]),
        'output'       => str_replace("Exit status : " . $matches[0], '', $complete_output)
     );
}

foreach ($wallets as $w) {
	foreach($pass as $p) {		
		$r = liveExecuteCommand('openssl enc -d -aes-256-cbc -md md5 -a -in ./'.trim($w).' > '.trim($w).'-decrypt -pass pass:'.trim($p).'');
		if($r['exit_status'] === 0) {
			echo $p.PHP_EOL;
			echo $w.PHP_EOL;
			exit;
		}
	}
}
