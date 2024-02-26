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

// cd to project root and revert after
$dirCurrent = getcwd();
chdir( __DIR__ . '/../' );
$newRelease = false;

ini_set( 'include_path', 'src/library/mumsys/' );
require_once('Mumsys_Loader.php');
spl_autoload_extensions( '.php' );
spl_autoload_register( array('Mumsys_Loader', 'autoload') );

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
 *
 * @param string $version Version string for the target file name
 *
 * @return bool True on success or false on error or phar file already exists
 */
function makePhar( $version = '0.0.0' )
{
    $pharFile = 'deploy/multirename-' . $version . '.phar';
    if ( file_exists( $pharFile ) ) {
        return false;
    }

    $phar = new Phar(
        $pharFile,
        FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
        "multirename.phar"
    );

    $phar->startBuffering();

    //$stub = "#!/usr/bin/env php\n" . $phar->createDefaultStub('multirename.php','multirename.php');
    //file_put_contents('build/stub.php', $stub);
    exec('php -w build/stub.php > build/stub.php.min');
    $phar->setStub( "#!/usr/bin/env php\n" . file_get_contents( 'build/stub.php.min' ) );
    //$phar->setStub( $stub );

    $pRoot = '';
    $pLibFrom = 'src/library/mumsys/';
    $pLibTo = 'library/mumsys/';
    $filesForBuild = array(
        'src/multirename.php'                               => $pRoot, // root level
        $pLibFrom . 'Mumsys_Abstract.php'                   => $pLibTo,
        $pLibFrom . 'Mumsys_Exception.php'                  => $pLibTo,
        $pLibFrom . 'Mumsys_Loader_Exception.php'           => $pLibTo,
        $pLibFrom . 'Mumsys_Loader.php'                     => $pLibTo,
        $pLibFrom . 'Mumsys_Php_Globals.php'                => $pLibTo,
        $pLibFrom . 'Mumsys_File_Interface.php'             => $pLibTo,
        $pLibFrom . 'Mumsys_File_Exception.php'             => $pLibTo,
        $pLibFrom . 'Mumsys_File.php'                       => $pLibTo,
        $pLibFrom . 'Mumsys_Logger_Writer_Interface.php'    => $pLibTo,
        $pLibFrom . 'Mumsys_Logger_Decorator_Interface.php' => $pLibTo,
        $pLibFrom . 'Mumsys_Logger_Decorator_Abstract.php'  => $pLibTo,
        $pLibFrom . 'Mumsys_Logger_Decorator_Messages.php'  => $pLibTo,
        $pLibFrom . 'Mumsys_Logger_Interface.php'           => $pLibTo,
        $pLibFrom . 'Mumsys_Logger_Exception.php'           => $pLibTo,
        $pLibFrom . 'Mumsys_Logger_Abstract.php'            => $pLibTo,
        $pLibFrom . 'Mumsys_Logger_File.php'                => $pLibTo,
        $pLibFrom . 'Mumsys_GetOpts_Exception.php'          => $pLibTo,
        $pLibFrom . 'Mumsys_GetOpts.php'                    => $pLibTo,
        $pLibFrom . 'Mumsys_FileSystem_Interface.php'       => $pLibTo,
        $pLibFrom . 'Mumsys_FileSystem_Exception.php'       => $pLibTo,
        $pLibFrom . 'Mumsys_FileSystem_Common_Abstract.php' => $pLibTo,
        $pLibFrom . 'Mumsys_FileSystem.php'                 => $pLibTo,
        $pLibFrom . 'Mumsys_Multirename_Exception.php'      => $pLibTo,
        $pLibFrom . 'Mumsys_Multirename.php'                => $pLibTo,
    );

    $filesForBuildCnt = count($filesForBuild);
    foreach ( $filesForBuild as $fileSrc => $fileGoesTo ) {
        $fileTmp = 'tmp/' . basename( $fileSrc ) . '.min';
        exec( 'php -w ' . $fileSrc . ' > ' . $fileTmp );
        $phar->addFile( $fileTmp, $fileGoesTo . basename( $fileSrc ) );
        echo '.';

        unlink( $fileTmp);
    }
    echo PHP_EOL;

    $phar->stopBuffering();

    return true;
}


/**
 * Scan file and replace all after "## Usage options" with multirename --help output
 *
 * @param string $keyword
 *
 * @return bool Returns true if new content was generated otherwise false
 */
function updUsageFile( $keyword = '## options tag' )
{
    $newUsage = '';

    $file = './docs/' . 'USAGE.txt';
    $hashOld = md5_file( $file );

    $lines = file( $file );
    foreach ( $lines as $key => $line ) {
        if ( $line === $keyword . PHP_EOL  ) {
            $newUsage .= $keyword . PHP_EOL;
            break;
        }

        $newUsage .= $line;
    }
    $newUsage .= PHP_EOL;

    $list = Mumsys_Multirename::getSetup( true );
    $list['--help'] = 'Show this help';
    $wrap = 72;
    $indentOption = '    ';
    $indentComment = "        ";
    foreach ( $list as $option => $desc ) {
        $needvalue = strpos( $option, ':' );
        $option = str_replace( ':', '', $option );

        if ( $needvalue ) {
            $option .= ' <yourValue/s>';
        }

        if ( $desc ) {
            $desc = $indentComment . wordwrap( $desc, $wrap, PHP_EOL . $indentComment );
        }

        $newUsage .= $indentOption . $option . PHP_EOL . $desc . '' . PHP_EOL . PHP_EOL;
    }
    $newUsage .= PHP_EOL . PHP_EOL;

    file_put_contents( $file, $newUsage );

    $hashNew = md5_file( $file );

    if ( $hashOld === $hashNew ) {
        return false; // no changes
    } else {
        return true;
    }
}


/**
 * creates the README.md file for github incl the Summary/ TOC
 */
function makeReadmeMd()
{
    global $docs;
    $summary = [];
    $content = '';
    $target = './README.md';
    // TOC tree to show. Value 1 - ~6 (for: h1-h6)
    $levelsToShow = 3;

    $summary = array();
    $content = '';

    foreach ($docs as $doc => $wiki)
    {
        if (is_int($doc)) {
            $doc = $wiki;
        }

        $docUsage = false;

        $lines = file('./docs/' . $doc);

        foreach ($lines as $idx => $line)
        {
            $content .= $line;

            $linktext = checkDocTocLine( $line, $levelsToShow, '+', "\n", $idx );
            if ( $linktext ) {
                $summary[] = $linktext;
            }

//            // build summary list (h1-h6)
//            if ( $line !== "# Summary\n"
//                && preg_match( '/^(#|##|###|####|#####|######)?( \w)+/i', $line, $matches ) ) {
//                if ( ($cntIndent = strlen( $matches[1] ) - 1 ) < 0 ) {
//                    $cntIndent = 0;
//                }
//
//                if ($levelsToShow -1 < $cntIndent ) {
//                    continue;
//                }
//
//                $prefix = strstr($line, ' ', true);
//                $line = str_replace('#', '', $line);
//
//                if (!isset($prefix[0])) { // ???
//                    echo 'ERROR with line (' . ($idx+1) . '): "' . $line . '"' . PHP_EOL;
//                }
//
//                $indent = str_repeat( "\t", $cntIndent );
//                $prefix = str_replace($matches[1], $indent . '-', $prefix);
//
//                $line = trim($line);
//                $linkLine = strtolower($line);
//
//                //orig:$linkLine = preg_replace('/(\s|\W)+/', '-', $linkLine);
//
//                $search = array(' ', '!', '&', ':');
//                $replace = array('-', '-', '', '');
//                $linkLine = str_replace( $search, $replace, $linkLine );
//                $linkLine = preg_replace('/(\s)+/', '-', $linkLine);
//                //echo "$linkLine :: $line\n";
//
//                $linkLine = trim($linkLine, '-&:!');
//
//                // the docs have *nix ending: \n
//                $summary[] = $prefix . ' [' . $line . '](#' . $linkLine . ')' . "\n";
//            }

        }
    }

    $summary = '## Summary' . "\n" . "\n" .  implode('', $summary);
    $content = str_replace('## Summary', $summary, $content);
    // replace makers
    $content = str_replace(array('##VERSIONSTRING##'), array(Mumsys_Multirename::VERSION), $content);
    file_put_contents($target, $content);
}



/**
 * Check a line of a markdown document and return the text as link if it is a headline
 *
 * @param string $line Text line to inspect
 * @param int $levelsToShow Headlines of 1 to 6 for h1-h6 headlines in html
 * @param string $listSign List starts with a - or +
 * @param string $docEOL Line break char. Default "\n"
 * @param type $lineNumber Optional line number for error output
 * @return string Empty string for no headline or the text as link with anker
 */
function checkDocTocLine($line, $levelsToShow=6, $listSign='+', $docEOL ="\n", $lineNumber=0) {

     $tocLine = '';

     if ( $line[0] !== '#' ) {
        return $tocLine; // not a toc line
    }

    $matches = null;
    if ( preg_match( '/^(#|##|###|####|#####|######)?( \w)+/i', $line, $matches ) ) {
        if ( ($cntIndent = strlen( $matches[1] ) - 1 ) < 0 ) {
            $cntIndent = 0;
        }

        if ( $levelsToShow - 1 < $cntIndent ) {
            return $tocLine;
        }

        $prefixA = strstr( $line, ' ', true );// the first char after #
        $line = str_replace( '#', '', $line );

        if ( !isset( $prefixA[0] ) ) {
            echo 'ERROR with line (' . ($idx+1) . '): "' . $line . '"' . PHP_EOL;
        }

        $indent = str_repeat( "    ", $cntIndent );
        $prefix = str_replace( $matches[1], $indent . $listSign, $prefixA );

        $line = trim( $line );
        $linkLine = strtolower( $line );

        //orig:$linkLine = preg_replace('/(\s|\W)+/', $listSign, $linkLine);

        $search = array(' ', '!', '&', ':', '(', ')', '/');
        $replace = array('-', '-', '', '', '', '', '');
        $linkLine = str_replace( $search, $replace, $linkLine );
        $linkLine = preg_replace( '/(\s)+/', '-', $linkLine );

        $linkLine = trim( $linkLine, '-&:!#' );

        $tocLine = $prefix . ' [' . $line . '](#' . $linkLine . ')' . $docEOL;
    }

    return $tocLine;
}


/**
 * Creates wiki documentation files
 */
function mkWikiMd()
{
    global $docs;

    foreach ( $docs as $doc => $wikifile ) {
        if ( is_int( $doc ) ) {
            continue;
        }

        $text = str_replace(
            array('##VERSIONSTRING##'),
            array(Mumsys_Multirename::VERSION),
            file_get_contents( 'docs/' . $doc )
        );
        file_put_contents( $wikifile, $text );
    }
}


try
{
    $cliOptsCfg = array(
        'install' => 'Compile the phar file',
        'clean' => 'Removes the phar and created tmp files',
        'deploy' => array(
            'Generates the phar file and updates the wiki docs and /README.md file.' . PHP_EOL => '',

            '--compress' => 'Flag; If given e.g: `php make.php deploy '
            . '--compress` it creates e.g `deploy/multirename-CURRENT_VERSION.tgz` '
            . 'including the phar and the README.md',
        ),
        '--help|-h' => 'Show this help',
    );
    $cliOpts = new Mumsys_GetOpts( $cliOptsCfg );
    $cliOptsResult = $cliOpts->getResult();

    if ( isset( $cliOptsResult['help'] ) ) {
        echo $cliOpts->getHelp();
        exit(); // only here stop to prevent action calls
    }

    if ( $cliOptsResult === array() ) {
        $cliOptsResult['help'] = true;
    }

    //require_once 'Mumsys_Multirename.php';
    echo 'Make file for ' . Mumsys_Multirename::getVersion() . PHP_EOL . PHP_EOL;
    $version = Mumsys_Multirename::VERSION;


    foreach ( $cliOptsResult as $action => $actionOptions ) {
        switch ( $action ) {
            case 'install':
                echo 'run: ' . $action . ' start:' . PHP_EOL;
                $makePharStatus = makePhar( $version );

                if ( !file_exists( 'deploy/multirename-' . $version . '.phar' ) ) {
                    echo 'deploy/multirename-' . $version . '.phar not found. '
                        .'Creation failed!
                    ';
                } else {
                    if ( $makePharStatus ) {
                        echo 'If you dont see any errors... multirename-' . $version
                            . '.phar was created successfully' . PHP_EOL
                            . PHP_EOL;
                    } else {
                        echo 'FAIL: deploy/multirename-' . $version . '.phar '
                            .' already exists. ' . PHP_EOL
                            . 'Remove/ rename the file to generate '
                            . 'a new phar file.'. PHP_EOL
                            . PHP_EOL;
                    }

                    echo '#### test it:' . PHP_EOL
                        . '# chmod +x deploy/multirename-' . $version . '.phar' . PHP_EOL
                        . '# ./deploy/multirename-' . $version . '.phar --help' . PHP_EOL
                        . '#' . PHP_EOL
                        . '### make globaly available' . PHP_EOL
                        . '# mv deploy/multirename-' . $version . '.phar /usr/local/bin/multirename' . PHP_EOL
                        . '# multirename --help' . PHP_EOL
                        . PHP_EOL
                        . PHP_EOL
                        ;
                }
                echo 'run: ' . $action . ' end.' . PHP_EOL;
                break;

            case 'clean':
                echo 'run: ' . $action . ' start:' . PHP_EOL;
                $cleanList = array(
                    'build/stub.php.min' => 'Precompiled by phar extension',
                    'deploy/multirename.phar' => 'The created multirename.phar file',
                    'deploy/multirename-' . $version . '.phar' => 'The created multirename.VERSION.phar file',
                    'deploy/multirename-' . $version . '.tgz' => 'The created compressed package',
                );
                foreach ( $cleanList as $idx => $message ) {
                    if ( file_exists( $idx ) ) {
                        unlink( $idx );
                        printf( 'Removed file "%1$s": %2$s%3$s', $idx, $message, PHP_EOL );
                    }
                }
                echo 'run: ' . $action . ' end.' . PHP_EOL . PHP_EOL;
                break;

            case 'deploy':
                echo 'run: ' . $action . ' start:' . PHP_EOL;
                // for deployment of a new releases or updating the docs
                makePhar( $version );

                if ( updUsageFile('### Usage options (--help)') ) {
                    echo 'USAGE.txt updated' . PHP_EOL;
                } else {
                    echo 'USAGE.txt same' . PHP_EOL;
                }

                makeReadmeMd();
                echo 'README.md created' . PHP_EOL;

                mkWikiMd();
                echo 'Wiki files created' . PHP_EOL;

                $compress = $actionOptions['compress'] ?? false;
                if ( $compress ) {
                    // rename('build/multirename.phar', 'build/multirename-'.$version.'.phar');
                    $tgzFile = 'deploy/multirename-'.$version.'.tgz';
                    if ( file_exists( $tgzFile ) ) {
                        echo 'Not created! EXISTS: tgz in deploy/ already exists. ';
                        echo 'Asume only one package per version.' . PHP_EOL;
                    } else {
                        $cmd = 'tar -czf "' . $tgzFile . '" '
                            . 'deploy/multirename-' . $version . '.phar '
                            . 'docs/LICENSE.txt '
                            . 'README.md'
                            ;
                        exec( $cmd );
                        echo 'tgz in deploy/ created' . PHP_EOL;
                    }
                }
                echo 'run: ' . $action . ' end.' . PHP_EOL . PHP_EOL;
                break;

            default:
                echo <<<EOTXT
    Please read the README.txt and INSTALL.txt befor you go on.
    Deployment tasks: Please read the CONTRIBUTE.txt informations

    Several actions possible, e.g: make.php clean install deploy --compress

    EOTXT;
                echo $cliOpts->getHelp();
                break;
        }
    }
} catch ( Exception $e ) {
    echo $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit( 1 );
}


chdir( $dirCurrent );
