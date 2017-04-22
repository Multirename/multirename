#!/usr/bin/env php
<?php

/**
 * Make for Multirename
 *
 * @license LGPL Version 3 http://www.gnu.org/licenses/lgpl-3.0.txt
 * @copyright (c) 2015 by Florian Blasel
 * @author Florian Blasel <flobee.code@gmail.com>
 *
 * @version 1.1.0 Created 2015-04-06
 */

// cd to project root
$dirCurrent = getcwd();
chdir(__DIR__ . '/../');
$newRelease = false;


ini_set('include_path', 'src/library/mumsys/');
require_once('Mumsys_Loader.php');
spl_autoload_extensions( '.php' );
spl_autoload_register( array( 'Mumsys_Loader', 'autoload'));


/**
 * relevant docs for wiki and readme.md ONLY for the stable/master branch at github or
 * for local, individual installations or bundles
 * Array value contains the location of the wiki file name
 */
$docs = array(
    'README.txt' => 'externals/multirename.wiki/Home.md',
    // 'SUMMARY.txt', // content to be generated where the "# Summary" tag is
    'FEATURES.txt'  => 'externals/multirename.wiki/1_Features-of-multirename.md',
    'EXAMPLES.txt'  => 'externals/multirename.wiki/2_Examples-for-multirename.md',
    'INSTALL.txt'   => 'externals/multirename.wiki/3_Installing-multirename.md',
    'USAGE.txt'     => 'externals/multirename.wiki/4_Usage-of-multirename.md',
    'CONTRIBUTE.txt'=> 'externals/multirename.wiki/5_Contributions.md',
    'AUTHORS.txt'   => 'externals/multirename.wiki/6_Contribution_Authors.md',
    'HISTORY.txt'   => 'externals/multirename.wiki/7_History-of-multirename.md',
    'CHANGELOG.txt' => 'externals/multirename.wiki/8_0_Changelog-of-multirename.md',
    'BUGS.txt' => 'externals/multirename.wiki/8_1_Bugs-of-multirename.md',
    'LICENSE.txt'   => 'externals/multirename.wiki/9_License-for-multirename.md',
);


/**
 * Creates the multirename.phar file
 */
function makePhar($version='0.0.0')
{
    $phar = new Phar(
        "deploy/multirename-" . $version . ".phar",
        FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
        "multirename.phar"
    );

    $phar->startBuffering();

    //$stub = "#!/usr/bin/env php\n" . $phar->createDefaultStub('multirename.php','multirename.php');
    //file_put_contents('build/stub.php', $stub);
    exec('php -w build/stub.php > build/stub.php.min');
    $phar->setStub("#!/usr/bin/env php\n" . file_get_contents('build/stub.php.min') );
    //$phar->setStub( $stub );

    $libFiles = array(
        'Mumsys_Abstract',
        'Mumsys_Exception',
        'Mumsys_Loader_Exception',
        'Mumsys_Loader',
        'Mumsys_Php_Globals',
        'Mumsys_File_Exception',
        'Mumsys_File_Interface',
        'Mumsys_File',
        'Mumsys_Logger_Writer_Interface',
        'Mumsys_Logger_Decorator_Interface',
        'Mumsys_Logger_Decorator_Abstract',
        'Mumsys_Logger_Decorator_Messages',
        'Mumsys_Logger_Interface',
        'Mumsys_Logger_Exception',
        'Mumsys_Logger_Abstract',
        'Mumsys_Logger_File',
        'Mumsys_GetOpts_Exception',
        'Mumsys_GetOpts',
        'Mumsys_FileSystem_Interface',
        'Mumsys_FileSystem_Exception',
        'Mumsys_FileSystem_Common_Abstract',
        'Mumsys_FileSystem',
        'Mumsys_Multirename_Exception',
        'Mumsys_Multirename',
    );

    $phar->addFile('src/multirename.php', 'multirename.php');

    foreach ($libFiles as $class) {
        $phar->addFile('src/library/mumsys/' . $class . '.php', 'library/mumsys/' . $class . '.php');
    }

    $phar->stopBuffering();

    return true;
}


function updUsageFile($keyword='## Usage options (--help)') {
    // task: scann file and replace all after "## Usage options" with multirename --help

    $newUsage = '';

    $file = './docs/' . 'USAGE.txt';

    $lines = file($file);
    foreach ($lines as $key => $line) {
        if ($line === $keyword.PHP_EOL) {
            $newUsage .= $keyword . PHP_EOL;
            break;
        }

        $newUsage .= $line;
    }
    $newUsage .= PHP_EOL;


    $list = Mumsys_Multirename::getSetup(true);
    $list['--help'] = 'Show this help';
    $wrap = 72;
    $indentOption = '    ';
    $indentComment = "        ";
    foreach($list as $option => $desc)
    {
        $needvalue = strpos($option, ':');
        $option = str_replace(':', '', $option);

        if ($needvalue) {
            $option .= ' <yourValue/s>';
        }

        if ($desc) {
            $desc = $indentComment . wordwrap($desc, $wrap, PHP_EOL . $indentComment);
        }

        $newUsage .= $indentOption . $option . PHP_EOL
            . $desc . '' . PHP_EOL . PHP_EOL;
    }
    $newUsage .= PHP_EOL . PHP_EOL;

    file_put_contents($file, $newUsage);
}


/**
 * creates the README.md file for github
 */
function makeReadmeMd()
{
    global $docs;
    $summary = [];
    $content = '';
    $target = './README.md';

    $summary = [];
    $content = '';

    foreach ($docs as $doc => $wiki)
    {
        if (is_int($doc)) {
            $doc = $wiki;
        }

        $docUsage = false;

        $lines = file('./docs/' . $doc);

        foreach ($lines as $line)
        {
            $content .= $line;

            // build summary list
            if ($line !== "# Summary\n" && preg_match('/^(#|##)?( \w)+/i', $line, $matches))
            {
                $prefix = strstr($line, ' ', true);
                $line = str_replace('#', '', $line);

                if (!isset($prefix[0])) {
                    echo 'ERROR with line: "' . $line . '"' . PHP_EOL;
                }

                $prefix[0] = str_replace('#', '-', $prefix[0]);
                $prefix = str_replace('-#', "\t-", $prefix);
                $line = trim($line);

                $linkLine = strtolower($line);

                $linkLine = preg_replace('/(\s|\W)+/', '-', $linkLine);
                $linkLine = trim($linkLine, '-');

                // the docs have *nix ending
                $summary[] = $prefix . ' [' . $line . '](#' . $linkLine . ')' . "\n";
            }

        }
    }

    $summary = '# Summary' . PHP_EOL . PHP_EOL .  implode('', $summary);
    $content = str_replace('# Summary', $summary, $content);

    // replace makers
    $content = str_replace(array('##VERSIONSTRING##'), array(Mumsys_Multirename::VERSION), $content);
    file_put_contents($target, $content);
}


/**
 * Creates wiki documentation files
 */
function mkWikiMd()
{
    global $docs;

    foreach ($docs as $doc => $wikifile)
    {
        if (is_int($doc)) {
            continue;
        }

        $text = file_get_contents('docs/' . $doc);
        $text = str_replace(array('##VERSIONSTRING##'), array(Mumsys_Multirename::VERSION), $text);
        file_put_contents($wikifile, $text);
    }

}



try
{
    require_once 'Mumsys_Multirename.php';
    echo 'Make file for ' . Mumsys_Multirename::getVersion() . PHP_EOL . PHP_EOL;
    $version = Mumsys_Multirename::VERSION;
    $testRelease = trim(@$_SERVER['argv'][2]);
    if ($testRelease == $version) {
        $newRelease = $version;
    }

    switch (@$_SERVER['argv'][1])
    {
        case 'install':
            makePhar($version);

            if (!file_exists('deploy/multirename-' . $version . '.phar')) {
                echo 'deploy/multirename-'.$version.'.phar not found. Creation failed!
                ';
            } else {
                echo 'If you dont see any errors... multirename-'.$version.'.phar was created successfully

#### test it:
# chmod +x deploy/multirename-'.$version.'.phar
# ./deploy/multirename-'.$version.'.phar --help
#
### make globaly available
# mv build/multirename-'.$version.'.phar /usr/local/bin/multirename
# multirename --help


';

            }

            break;

        case 'clean':
            @unlink('build/stub.php.min');
            @unlink('deploy/multirename.phar');
            @unlink('deploy/multirename-'.$version.'.phar');
            echo 'clean complete' . PHP_EOL;

            break;

        case 'deploy':
            // for deployment of a new releases or updating the docs
            makePhar($version);

            updUsageFile('## Usage options (--help)');
            echo 'USAGE.txt updated' . PHP_EOL;

            makeReadmeMd();
            echo 'README.md created' . PHP_EOL;

            mkWikiMd();
            echo 'Wiki files created' . PHP_EOL;

            if ($newRelease) {
                rename('build/multirename.phar', 'build/multirename-'.$version.'.phar');

                $cmd = 'tar -czf deploy/multirename-'.$version.'.tgz '
                    . 'build/multirename-'.$version.'.phar '
                    . 'docs/LICENSE.txt '
                    . 'README.md'
                    ;
                exec($cmd);
                echo 'tgz in deploy/ created' . PHP_EOL;
            }

            echo PHP_EOL . 'done.' . PHP_EOL;

            break;

        default:
            echo <<<EOTXT
Please read the README.txt and INSTALL.txt befor you go on.
Deployment tasks: Please read the CONTRIBUTE.txt informations

Options:
    php make.php install
    php make.php clean
    php make.php deploy [optional: VersionID to create a bundled tar file]


EOTXT;
            break;
    }

} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}


chdir($dirCurrent);