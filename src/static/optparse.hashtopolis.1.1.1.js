/**
 * Hashcat Command Validation (based on Hashcat v6.1.1)
 *
 * This plugin provides validation of Hashcat command
 * require OptparseJS Library: https://github.com/shivanraptor/optparse-js
 *
 * @package    optparse_hashtopolis
 * @copyright  2020 Raptor Kwok
 * @license    MIT License
 * @website    https://github.com/shivanraptor/optparse-js
 * @version	   0.3
 */

// TODO: Change hardcoded values to ENUM

let switches = [
	['-m', '--hash-type NUMBER', "Hash-type"],
	['-a', '--attack-mode NUMBER', "Attack-mode"],
	['-V', '--version', "Print version"],
	['-h', '--help', "Print help"],
	['--quiet', "Suppress output"],
	['--hex-charset', "Assume charset is given in hex"],
	['--hex-salt', "Assume salt is given in hex"],
	['--hex-wordlist', "Assume words in wordlist are given in hex"],
	['--force', "Ignore warnings"],
	['--status', "Enable automatic update of the status screen"],
	['--status-json', "Enable JSON format for status output"],
	['--status-timer NUMBER', "Sets seconds between status screen updates to X"],
	['--stdin-timeout-abort NUMBER', "Abort if there is no input from stdin for X seconds"],
	['--machine-readable', "Display the status view in a machine-readable format"],
	['--keep-guessing', "Keep guessing the hash after it has been cracked"],
	['--self-test-disable', "Disable self-test functionality on startup"],
	['--loopback', "Add new plains to induct directory"],
	['--markov-hcstat2 FILE', "Specify hcstat2 file to use"],
	['--markov-disable', "Disables markov-chains, emulates classic brute-force"],
	['--markov-classic', "Enables classic markov-chains, no per-position"],
	['-t', '--markov-threshold NUMBER', "Threshold X when to stop accepting new markov-chains"],
	['--runtime NUMBER', "Abort session after X seconds of runtime"],
	['--session MESSAGE', "Define specific session name"],
	['--session', "Restore session from --session"], 
	['--restore-disable', "Do not write restore file"],
	['--restore-file-path FILE', "Specific path to restore file"],
	['-o', '--outfile FILE', "Define outfile for recovered hash"],
	['--outfile-format CSV_INT', "Outfile format to use, separated with commas"],
	['--outfile-autohex-disable', "Disable the use of $HEX[] in output plains"],
	['--outfile-check-timer NUMBER', "Sets seconds between outfile checks to X"],
	['--wordlist-autohex-disable', "Disable the conversion of $HEX[] from the wordlist"],
	['-p', '--separator SINGLE_CHAR', "Separator char for hashlists and outfile"],
	['--stdout', "Do not crack a hash, instead print candidates only"],
	['--show', "Compare hashlist with potfile; show cracked hashes"],
	['--left', "Compare hashlist with potfile; show uncracked hashes"],
	['--username', "Enable ignoring of usernames in hashfile"],
	['--remove', "Enable removal of hashes once they are cracked"],
	['--remove-timer NUMBER', "Update input hash file each X seconds"],
	['--potfile-disable', "Do not write potfile"],
	['--potfile-path FILE', "Specific path to potfile"],
	['--encoding-from ENCODING', "Force internal wordlist encoding from X"],
	['--encoding-to ENCODING', "Force internal wordlist encoding to X"],
	['--debug-mode NUMBER', "Defines the debug mode (hybrid only by using rules)"],
	['--debug-file FILE', "Output file for debugging rules"],
	['--induction-dir FILE', "Specify the induction directory to use for loopback"], // TODO: Change to DIRECTORY filter
	['--outfile-check-dir FILE', "Specify the outfile directory to monitor for plains"], // TODO: Change to DIRECTORY filter
	['--logfile-disable', "Disable the logfile"],
	['--hccapx-message-pair NUMBER', "Load only message pairs from hccapx matching X"],
	['--nonce-error-corrections NUMBER', "The BF size range to replace AP's nonce last bytes"],
	['--keyboard-layout-mapping FILE', "Keyboard layout mapping table for special hash-modes"],
	['--truecrypt-keyfiles FILE', "TrueCrypt Keyfiles to use, separated with commas"],
	['--veracrypt-keyfiles FILE', "VeraCrypt Keyfiles to use, separated with commas"],
	['--veracrypt-pim-start NUMBER', "VeraCrypt personal iterations multiplier start"],
	['--veracrypt-pim-stop NUMBER', "VeraCrypt personal iterations multiplier stop"],
	['-b', '--benchmark', "Run benchmark of selected hash-modes"],
	['--benchmark-all', "Run benchmark of all hash-modes (requires -b)"], // TODO: Check existence of -b flag
	['--speed-only', "Return expected speed of the attack, then quit"],
	['--progress-only', "Return ideal progress step size and time to process"],
	['-c', '--segement-size NUMBER', "Sets size in MB to cache from the wordfile to X"],
	['--bitmap-min NUMBER', "Sets minimum bits allowed for bitmaps to X"],
	['--bitmap-max NUMBER', "Sets maximum bits allowed for bitmaps to X"], // TODO: Compare with --bitmap-min
	['--cpu-affinity CSV_INT', "Locks to CPU devices, separated with commas"],
	['--hook-threads NUMBER', "Sets number of threads for a hook (per compute unit)"],
	['--example-hashes', "Show an example hash for each hash-mode"],
	['--backend-ignore-cuda', "Do not try to open CUDA interface on startup"],
	['--backend-ignore-opencl', "Do not try to open OpenCL interface on startup"],
	['-I', '--backend-info', "Show info about detected backend API devices"],
	['-d', '--backend-devices CSV_INT', "Backend devices to use, separated with commas"],
	['-D', '--opencl-device-types CSV_INT', "OpenCL device-types to use, separated with commas"],
	['-O', '--optimized-kernel-enable', "Enable optimized kernels (limits password length)"],
	['-w', '--workload-profile NUMBER', "Enable a specific workload profile, see pool below"], // TODO: Check Workload Profile range
	['-n', '--kernel-accel NUMBER', "Manual workload tuning, set outerloop step size to X"],
	['-u', '--kernel-loops NUMBER', "Manual workload tuning, set innerloop step size to X"],
	['-T', '--kernel-threads NUMBER', "Manual workload tuning, set thread count to X"],
	['--backend-vector-width NUMBER', "Manually override backend vector-width to X"],
	['--spin-damp PERCENT', "Use CPU for device synchronization, in percent"],
	['--hwmon-disable', "Disable temperature and fanspeed reads and triggers"], 
	['--hwmon-temp-abort NUMBER', "Abort if temperature reaches X degrees Celsius"], 
	['--scrypt-tmto NUMBER', "Manually override TMTO value for scrypt to X"],
	['-s', '--skip NUMBER', "Skip X words from the start"],
	['-l', '--limit', "Limit X words from the start + skipped words"],
	['--keyspace', "Show keyspace base:mod values and quit"],
	['-j', '--rule-left FILE', "Single rule applied to each word from left wordlist"],
	['-k', '--rule-right FILE', "Single rule applied to each word from right wordlist"],
	['-r', '--rules-file FILE', "Multiple rules applied to each word from wordlists"],
	['-g', '--generate-rules NUMBER', "Generate X random rules"],
	['--generate-rules-func-min NUMBER', "Force min X functions per rule"],
	['--generate-rules-func-max NUMBER', "Force max X functions per rule"], // TODO: Compare MIN and MAX values
	['--generate-rules-seed NUMBER', "Force RNG seed set to X"],
	['-1', '--custom-charset1 MESSAGE', "User-defined charset ?1"], // TODO: Check Charset
	['-2', '--custom-charset2 MESSAGE', "User-defined charset ?2"], // TODO: Check Charset
	['-3', '--custom-charset3 MESSAGE', "User-defined charset ?3"], // TODO: Check Charset
	['-4', '--custom-charset4 MESSAGE', "User-defined charset ?4"], // TODO: Check Charset
	['-i', '--increment', "Enable mask increment mode"],
	['--increment-min NUMBER', "Start mask incrementing at X"],
	['--increment-max NUMBER', "Stop mask incrementing at X"], // TODO: Compare MIN and MAX values
	['-S', '--slow-candidates', "Enable slower (but advanced) candidate generators"],
	['--brain-server', "Enable brain server"],
	['--brain-server-timer NUMBER', "Update the brain server dump each X seconds (min:60)"],
	['-z', '--brain-client', "Enable brain client, activates -S"],
	['--brain-client-features NUMBER', "Define brain client features, see below"], // TODO: Check brain client features range
	['--brain-host MESSAGE', "Brain server host (IP or domain)"], // TODO: Check IP or Domain format
	['--brain-port NETWORK_PORT', "Brain server port"],
	['--brain-password MESSAGE', "Brain server authentication password"], 
	['--brain-session HEX', "Overrides automatically calculated brain session"],
	['--brain-session-whitelist CSV_HEX', "Allow given sessions only, separated with commas"],
];
let defaultOptions = {
	debug: false,
	ruleFiles: [],
	attackType: -1,
	hashMode: -1,
	posArgs: [], // Positional Arguments
	customCharset1: '',
	customCharset2: '',
	customCharset3: '',
	customCharset4: '',
	unrecognizedFlag: []
};
var options = defaultOptions;

var parser = new optparse.OptionParser(switches);

// =======================================================================================
// Custom Hashtopolis-specific Filters
// =======================================================================================
parser.filter('encoding', function(value) {
	// TODO: Complete Encoding List: http://www.iana.org/assignments/character-sets
	var encodings = ['us-ascii', 'iso-8859-1', 'iso-8859-2', 'iso-8859-3', 'iso-8859-4', 'iso-8859-5', 'iso-8859-6', 'iso-8859-7', 'iso-8859-8', 'iso-8859-9', 'iso-8859-10', 'iso_6937-2-add', 'jis_x0201', 'jis_encoding', 'shift_jis', 'bs_4730', 'sen_850200_c', 'it', 'es', 'din_66003', 'ns_4551-1', 'nf_z_62-010', 'iso-10646-utf-1', 'invariant', 'nats-sefi', 'nats-sefi-add', 'nats-dano', 'nats-dano-add', 'sen_850200_b', 'ks_c_5601-1987', 'euc-jp', 'iso-2022-kr', 'euc-kr', 'iso-2022-jp', 'iso-2022-jp-2', 'jis_c6220-1969-jp', 'jis_c6220-1969-ro', 'pt', 'greek7-old', 'latin-greek', 'latin-greek-1', 'iso-5427', 'jis_c6226-1978', 'inis', 'inis-8', 'inis-cyrillic', 'gb_1988-80', 'gb_2312-80', 'ns_4551-2', 'pt2', 'es2', 'jis_c6226-1983', 'greek7', 'asmo_449',  'iso-ir-90', 'jis_c6229-1984-a', 'jis_c6229-1984-b', 'jis_c6229-1984-b-add', 'iso_c6229-1984-hand', 'iso_c6229-1984-hand-add', 'jis_c6229-1984-kana', 'iso_2033-1983', 'ansx_x3.110-1983', 't.61-7bit', 't.61-8bit', 'ecma-cyrillic', 'csa_z243.4-1985-1', 'csa_z243.4-1985-2', 'csa_z243.4-1985-gr', 'iso-8859-6-e', 'iso-8859-6-i', 't.101-g2', 'iso-8859-8-e', 'iso-8859-8-i', 'csn_369103', 'jus_i.b1.002', 'iec_p27-1', 'greek-ccitt', 'iso_6937-2-25', 'gost_19768-74', 'iso_8859-supp', 'iso_10367-box', 'latin-lap', 'jis_x0212-1990', 'ds_2089', 'us-dk', 'dk-us', 'ksc5636', 'unicode-1-1-utf-7', 'iso-2022-cn', 'iso-2022-cn-ext', 'iso-8859-13', 'iso-8859-14', 'iso-8859-15', 'iso-8859-16', 'gbk', 'gb18030', 'gb2312', 'osd_ebcdic_df04_15', 'osd_edcdic_df03_irv', 'osd_ebcdic_df04_1', 'iso-11548-1', 'kz-1048', 'iso-10646-ucs-2', 'iso-10646-ucs-4', 'iso-10646-ucs-basic', 'iso-10646-unicode-latin1', 'iso-10646-j-1', 'iso-unicode-ibm-1261', 'iso-unicode-ibm-1268', 'iso-unicode-ibm-1276', 'iso-unicode-ibm-1264', 'iso-unicode-ibm-1265', 'unicode1-1', 'scsu', 'utf-7', 'big5', 'koi8-r', 'utf-8', 'utf-16', 'utf-16be', 'utf-16le', 'utf-32', 'utf-32be', 'utf-32le', 'cesu-8', 'bocu-1', 'hp-roman8', 'adobe-standard-encoding', 'ventura-us', 'ibm-symbols', 'adobe-symbol-encoding', 'macintosh', 'hz-gb-2312', 'windows-1258', 'tis-620', 'cp50220'];
	if(encodings.indexOf(value) == -1) {
		throw "Invalid encoding standards";
	}
	return value; 
});

parser.filter('network_port', function(value) {
	if(parseInt(value) <= 0 || parseInt(value) > 65536) {
		throw "Network port out of range (1 - 65536)";
	}
	return parseInt(value);
});

// =======================================================================================
// Option Parser (to be completed)
// =======================================================================================
parser.on('hash-type', function(name, value) {
	// console.log('Hash Type: ' + value);
	options.hashMode = parseInt(value); // TODO: Check Hash Mode
});
parser.on('attack-mode', function(name, value) {
	// console.log('Attack Mode: ' + value);
	if(parseInt(value) >= 0 && parseInt(value) <= 9) {
		options.attackType = parseInt(value);
	} else {
		throw "Invalid Attack Type";
	}
});
parser.on('rules-file', function(name, value) {
	options.ruleFiles.push(value);
});
parser.on('status-timer', function(name, value) {
	// console.log('Status Timer: ' + value);
});
parser.on('separator', function(name, value) {
	// console.log('Separator: ' + value);
});
parser.on('encoding-from', function(name, value) {
	// console.log('Encoding From: ' + value);
});
parser.on('cpu-affinity', function(name, value) {
	// console.log('CPU Affinity: ' + value);
});
parser.on('brain-session', function(name, value) {
	// console.log('Brain Session: ' + value);
});
parser.on('brain-session-whitelist', function(name, value) {
	// console.log('Brain Session-whitelist: ' + value);
});
parser.on('spin-damp', function(name, value) {
	// console.log('Spin Damp Percent: ' + value);
});
parser.on('custom-charset1', function(name, value) {
	// console.log('Custom Charset 1: ' + value);
	options.customCharset1 = value;
});
parser.on('custom-charset2', function(name, value) {
	// console.log('Custom Charset 2: ' + value);
	options.customCharset2 = value;
});
parser.on('custom-charset3', function(name, value) {
	// console.log('Custom Charset 3: ' + value);
	options.customCharset3 = value;
});
parser.on('custom-charset4', function(name, value) {
	// console.log('Custom Charset 4: ' + value);
	options.customCharset4 = value;
});
parser.on('print', function(value) {
	// console.log('PRINT: ' + value);
});
parser.on('debug', function() {
	options.debug = true;
});
parser.on(0, function(opt) {
	// console.log('The first non-switch option is: ' + opt);
	options.posArgs[0] = opt;
});
parser.on(1, function(opt) {
	// console.log('The second non-switch option is: ' + opt);
	options.posArgs[1] = opt;
});
parser.on(2, function(opt) {
	// console.log('The third non-switch option is: ' + opt);
	options.posArgs[2] = opt;
});
parser.on(3, function(opt) {
	// console.log('The fourth non-switch option is: ' + opt);
	options.posArgs[3] = opt;
});
parser.on(4, function(opt) {
	// console.log('The fifth non-switch option is: ' + opt);
	options.posArgs[4] = opt;
});
parser.on('*', function(opt, value) {
    // console.log('wild handler for ' + opt + ', value=' + value);
});
parser.on(function(opt) {
	// console.log('Unrecognized flag: ' + opt);
	options.unrecognizedFlag.push(opt);
});

// =======================================================================================
// Functions
// =======================================================================================
function startParse(cmd, isHashtopolis = true) {
	// resetting the options
	options = defaultOptions;
	options.ruleFiles = [];
	options.posArgs = [];
	options.unrecognizedFlag = [];
	
	var result = false;
	if(isHashtopolis) {
		args = cmd.replace('hashcat', '').trim().split(/ |=/g);
		try {
			parser.parse(args);
			result = validateHashtopolisCommand(options);
		} catch (e) {
			return {"result": false, "reason": "Value exception: " + e};
		}
	} else {
		parser.parse(args);
		result = validateHashcatCommand(options);
	}
	return result;
}

function validateHashtopolisCommand(opt) {
	// Pre-case Check
	if(opt.posArgs[0] != '#HL#') {
		return {"result": false, "reason": "Hashlist is missing"};
	}
	return validateHashcatCommand(opt);
}

function validateHashcatCommand(opt) {
	if(opt.unrecognizedFlag.length > 0) {
		return {"result": false, "reason": "Unrecognized Flag: " + opt.unrecognizedFlag.join(', ')};
	} else if(opt.attackType == 0) { // 0: Word List Attack
		// Required Dictionary
		if(opt.posArgs.length == 2) {
			// console.log('Dictionary: ' + opt.posArgs[1]);
			if(opt.ruleFiles.length == 0) {
				return {"result": true, "reason": "Word List Attack"};
			} else {
				return {"result": true, "reason": "Word List Attack with " +  opt.ruleFiles.length + " rule(s)."};
			}
		} else {
			return {"result": false, "reason": "Missing wordlist"};
		}
	} else if(opt.attackType == 1) { // 1: Combinator Attack
		// Required Left and Right Wordlist 
		if(opt.posArgs.length == 3) {
			// console.log('Left Wordlist: ' + opt.posArgs[1] + ', Right Wordlist: ' + opt.posArgs[2]);
			if(opt.ruleFiles.length == 0) {
				return {"result": true, "reason": "Combinator Attack"};
			} else {
				return {"result": false, "reason": "Combinator Attack cannot use with rules"};
			}
		} else {
			return {"result": false, "reason": "Required TWO Wordlist"};
		}
	} else if(opt.attackType == 3) { // 3: Bruteforce Attack
		if(opt.posArgs.length > 1) {
			if(opt.customCharset1 != '') {
				if(opt.posArgs[1].indexOf('?1') !== -1) {
					return {"result": true, "reason": "Bruteforce Attack with Character Set 1"};
				}
			}
			if(opt.customCharset2 != '') {
				if(opt.posArgs[1].indexOf('?2') !== -1) {
					return {"result": true, "reason": "Bruteforce Attack with Character Set 2"};
				}
			}
			if(opt.customCharset3 != '') {
				if(opt.posArgs[1].indexOf('?3') !== -1) {
					return {"result": true, "reason": "Bruteforce Attack with Character Set 3"};
				}
			}
			if(opt.customCharset4 != '') {
				if(opt.posArgs[1].indexOf('?4') !== -1) {
					return {"result": true, "reason": "Bruteforce Attack with Character Set 4"};
				}
			}
			
			// No custom character set
			return {"result": true, "reason": "Bruteforce Attack with pattern: " + opt.posArgs[1]};
		} else {
			return {"result": false, "reason": "Bruteforce Attack but missing pattern"};
		}
	} else {
		return {"result": false, "reason": "Missing / Unsupported Attack Type (Supported Types: 0, 1, 3)"};
	}
}