# Multirename

### multirename - A shell program written in PHP

This program is made to use as shell program and also for batch processing. 
This means: If you have tonns of files to be renamed or have always new files 
which must be renamed: This program is made for you! I use it only in cronjobs. 


Hello! Welcome to my program. I hope you will enjoy it!

Happy renaming!
Florian

:-)


Notice: Auto generated file. All documentation was bundled in the README.md.
For single text files have a look at the [docs](/flobee/multirename/tree/master/docs))
Versions: The documentation at the wiki belongs to the master branch / latest
stable release. This documentation belongs to the branch you switch to. The
latest version you will find at the "unstable" branch.


# Summary

- [Multirename](#multirename)
- [Features](#features)
- [Examples for Multirename](#examples-for-multirename)
	- [Simple replacements/ substitutions:](#simple-replacements-substitutions)
	- [For the vdr project this can help:](#for-the-vdr-project-this-can-help)
	- [Synology DTV TV recordings:](#synology-dtv-tv-recordings)
- [Install](#install)
	- [Downloads](#downloads)
	- [Pre-Install](#pre-install)
	- [Windows](#windows)
	- [Developer install](#developer-install)
	- [The program comes in two flavors](#the-program-comes-in-two-flavors)
- [Build](#build)
	- [Options](#options)
- [Usage of Multirename](#usage-of-multirename)
	- [Forword](#forword)
	- [Hints](#hints)
	- [Options](#options)
- [Contributions](#contributions)
	- [Contributors are welcome!](#contributors-are-welcome)
	- [Deployment](#deployment)
- [License](#license)
- [Authors](#authors)
	- [Core developers](#core-developers)
- [Bugs](#bugs)
- [About / History](#about-history)



# Features

-   Simple replacements or removes.
    Look for a sign and replace it with another one. E.g.: Change all spaces in
    filenames with an underscore (_).

-   Replacements using pcre engine (Perl compatible regular expressions).
    Regular expressions are patterns detection. With filenames it can help to
    split complex names to rebuild the filename you need. E.g.:
        
        "2015-01-01-20.00.00 The daily news.mpg"
        will become
        "daily_news_-_2015-01-01.mpg"

-   Find relevant files or directorys by a list of search keywords or regular 
    expressions.

-   Renaming including the path or breadcrumbs of the path the file belongs to.
    The path or the parts of it are always available to be used for the
    replacement.

-   Replacements including regular expressions and including path replacements
    in a regular exression. E.g.: /(%path2%)?(.*)?(\d{5})?/i
    Checkout the examples for more.

-   Creation of hard links or symlinks, relative or absolut instead of renaming
    the files. Including relocating in this case. "this" goes "../in/here/that"

-   Possibility to undo an action.

-   A test mode to check the results when starting a rename process.

-   Presets: Possibility to save/update/delete a configuration. E.g. for a later 
    usage or batch processing like cron, at, batch...

-   Scan for matches in a recusivly way or just files in the given directory.

-   Scan for all files or a given list of file extensions. E.g.: doc;docx;xls

-   More features to come. Some are already in the code marked as TODO.


# Examples for Multirename

## Simple replacements/ substitutions:

### Example:

Delete all spaces in filenames and replace it with an underscore and remove the 
word "this" if it exists:

    multirename --path 'YOUR_PATH' \
        --fileextensions '*' \
        --substitutions ' =_;this' \
        --keepcopy  \
        --test

    or:
    multirename --path 'YOUR_PATH' -e '*' -s ' =_;this' --keepcopy --test


### Beautifing hyphen, comma and colon?

You substitution could look like: 

    --substitutions '-=_-_;,=_-_;:=_-_'


### Show a saved configuration:

    multirename --from-config 'YOUR_PATH' --show-config --test


### Use a saved configuration

Use a saved configuration but test befor, then execute it and save the 
configuration again (YOUR_PATH belongs to the path where your file are):

    multirename --from-config 'YOUR_PATH' --test

    multirename --from-config 'YOUR_PATH' --set-config

As you can see, using --test is always a good option to avoid problems!



## For the vdr project this can help:

### Example 1:

Check the difference in simple recordings and series recordings in vdr.
The following example is for simple recordings!
You may split your recordings in sub-folders like "movies" and "series".
Change request for vdradmin-am already exists: http://projects.vdr-developer.org/issues/2137)

    multirename --path 'YOUR_RECORDING_PATH_FOR_MOVIES' \
        --fileextensions ts;m2t;mpg;mpeg; \
        --substitutions ' =_;#3A=_-_;:_=_-_;regex:/^(\d{5}|info|marks)$/i=../%path2%_-_$1;_-_marks=.marks;_-_info=.nfo' \
        --keepcopy  \
        --recursive \
        --sub-paths \
        --test
    
Would do e.g.:

    old: 00001.ts
    new: ../True_Grit.ts
    old: marks
    new: ../True_Grit.marks
    old: info
    new: ../True_Grit.nfo

The default pattern for vdr recordings and maybe created mpg's 00001.ts.mpg:
 
    regex:/^(\d{5}|\d{5}\.ts|info|marks)$/i


### Example 2:

This example is for my series. When recording i use different subfolders like: 
Movie, Series, Doc, DocSeries so i can run multirename automatically without 
much attention. At the end of the substitutions parameter you find some specific
words for german television i dont want to have.:

    multirename --path 'YOUR_PATH_TO_SERIES' \
        --fileextensions 'ts;m2t;mpg;mpeg' \
        --substitutions ' =_;#3A=_-_;:_=_-_;regex:/^(\d{5}|info|marks)$/i=../../%path3%_-_%path1%_-_%path2%_-_$1;regex:/(\d{4}-\d{2}-\d{2})(.*.rec)(.*)/i=$1$3;_-_marks=.marks;_-_info=.nfo;regex:/(.*)(,(_Science-Fiction|_Action|_Drama),_USA_\d)(.*)/=$1;,_Scienc_=_;,_Magazin,_D;Thema_u._a._-_=ua_;,_Mag_-_=_-_;,_Magazin,;,_Magazi;,_Mag;,_Wissensmagazin,_D;_u._a.:_=_ua_' \
        --keepcopy  \
        --recursive \
        --sub-paths \
        --test

Would do e.g. (in real i just create relative symlinks --link 'soft;rel' and 
don't rename the files!):

    old: 00001.ts ...TO:
    new. ../../The_Flash_-_2015-03-31_-_Der_Mann_in_Gelb_-_00001.ts

    old: marks ...TO:
    new: ../../The_Flash_-_2015-03-31_-_Der_Mann_in_Gelb.marks

    old: info ...TO:
    new: ../../The_Flash_-_2015-03-31_-_Der_Mann_in_Gelb.nfo



## Synology DTV TV recordings:

    multirename --path 'YOUR-SYNO-RECORDING-PATH' \
        -e "ts;m2t;mpg;mpeg"
        -s 'regex:/^(\d{4}-\d{2}-\d{2}_\d{4})?(.*)/i=$2_-_$1;regex:/(_.{1,20}_)+(.*?)(_-_\d{4}-\d{2}-\d{2}_\d{4})$/i=$2$3;regex:/(_-_)$/i; =_' \
        --sub-paths \
        --history \
        --test

Would do e.g.:

    old: 2014-08-16_1410_RTL2_Merlin.ts
    new: Merlin_-_2014-08-16_1410.ts



# Install

## Downloads

Getting the source you have serveral options:
[Download page for releases](https://github.com/flobee/multirename/releases)
https://github.com/flobee/multirename/releases


## Pre-Install
(*nix based systems)

Install php and required modules:

    # and the suggested packages
    apt-get install php5-cli php5-json

To be mentiond: The default shell:
For multirename it self: php is the shell. For shell scripts: /bin/dash was the 
default shell. Some output may be different when using bash. ash seems to be 
fine also.


## Windows

Please use cygwin (http://cygwin.com). Native support will be in the future)


### Quick install 
(Unix/Linux/BSD based systems)

    (copy & past)
    cd /tmp
    wget https://github.com/flobee/multirename/blob/testing/deploy/multirename\
    -1.2.0.tgz?raw=true -O multirename-1.2.0.tgz
    tar -xf multirename-1.2.0.tgz
    chmod +x multirename.phar
    mv multirename.phar /usr/local/bin/multirename
    multirename --help

    
## Developer install
    
Checkout the code and required library:

    git clone https://github.com/flobee/multirename.git
    cd multirename/
    # checkout and update master|testing|unstable
    sh ./helper/gitupdate.sh [optional: master|testing|unstable]
    

The files you will call directly (if not already done):

    chmod +x <file>


## The program comes in two flavors

    1) Standard php usage (the src directory must be/stay intact):

    2) Single file usage (using a *.jar like file which is called "phar")
        (you can delete all other files. Everything is in that file)


    1. Possible options to run:

    1.1: php /path/where/src/multirename.php [--help|options]
    1.2: /path/where/src/multirename [--help|options]

    1.3: Make it global available:
        Became root
        ln -s /path/where/src/multirename /usr/local/bin/
        multirename [--help|options]


    2. For users who just want to use the program. Possible options:

    2.1: /path/where/build/multirename.phar [--help|options]

    2.2: Make it global available:
        copy or move /path/where/build/multirename.phar to /usr/local/bin/multirename
        multirename [--help|options]

        Or (but build directory must be intact):
        ln -s /path/where/build/multirename.phar /usr/local/bin/multirename
        multirename [--help|options]



# Build
(build/make.php)


This is not a typical make but for most of you probably an interesting corner.

This shows you how to create your own multirename.phar file or building a new 
release.


## Options

1. php make.php install
2. php make.php clean
3. php make.php deploy


### 1.: php make.php install:

You decided to use the bundled version (all files and required libraries in
one file) for an easy usage. Use the file multirename.phar, thats all!

If you want to build it by yourself, run: php make.php install

Note: This requires php >= 5.4 and changes in your php.ini:
Changes in the php.ini for the cli only is ok. The php.ini for web/ live
enviroment: STOP: NOT OK!

    e.g.: /etc/php5/cli/php.ini; Change:
    ";phar.readonly = On" to
    "phar.readonly = 0" (drop semicolon, On to 0 (Off))

Then you are able to create your own multirename.phar file whichs bundles
all nessasary files for the program to one file (Like *.jar files) and
you can use that file which is more easy to handle.

If you dont see any errors, the "multirename.phar" were created.
Please check if its working by running:

    php multirename.phar --help

The first line must show: "[INFO] Usage:"

The performance of this bundle is nearly the same than using the standard
sources for a php application (some people say it boost the performance...)
Well :-) less disk IO can make sence.

On upgrades only "multirename.phar" needs to be updated. Checkout the deploy/
directory where i will add final versions.


### 2.: php make.php clean:

Drops files created within the make script


### 3.: php make.php deploy

Task for a new release of multirename or just updating the documentation. For 
more please have a look in the [contributions section](#contributions) or at the
[docs/](/flobee/multirename/tree/master/docs/CONTRIBUTE.txt)


# Usage of Multirename

## Forword

- At the moment only the filename without the file extension will be handled.

- Internally always the long options will be used. If you use short options, no 
problem, but if you want to see the saved configuration the long options will be 
shown and used.

For the moment (may be for all the time):

- A logger tracks all actions to a log file (default in /tmp/ (max.3MB) if you 
dont change it). This make sence in the current state of the program to find and 
debug open or not detected bugs for you, me....

- Configurations can be saved but only one config per path at the moment. (i'm 
still thinking about to extend it or not! It make things complicated and a GUI 
would be the next step to handle thouse parts. Maybe someone likes do it? :-)


## Hints

- When executing the program you may think something went wrong because it seems
that the programm hangs: Probably you have enabled the --keepcopy flag and there
is a hugh file which will be copied and not renamed! Dont break operations! This
can loose informations for recovery/ undo action. Have look at the log file 
first (tail -f /tmp/multirename.$USER.log) befor stop the process. 


## Options

    --test|-t
        Flag: Test before execute. You should use it as many as possible befor 
        pressing <enter>!

    --path|-p
        Path to scann for files (tailing slash is important!); Required

    --fileextensions|-e
        Semicolon separated list of file extensions to scan for eg. "avi;AVI;mpg;
        MPG" or "*" (with quotes) for all files; Required

    --substitutions|-s
        Semicolon separated list with key=value (search=replace) pairs for the 
        substitution. E.g.: --substitutions 
        'ä=ae;ö=oe;ß=ss; =_;regex:/^(\d{5})$/i=x_path_-_%path2%_-_match_-_$1'
        As simple feature you can use %path1%...%pathN% parameters to substitute 
        path informations in replacement value the file belongs to.
        For more information see --sub-paths. If you decide later to use 
        --sub-paths with your existing substitutions it can became strange side 
        effects when enabling it. Check/ test the results first; Required

    --sub-paths
        Flag; Enable substitution for paths. A feature for the substitution: 
        Breadcrumbs of the --path can be found/ substituted with %path1% - 
        %pathN% in reverse. If you want to rename files and want to add the 
        folder the file belongs to you can use %path1%. One folder above is 
        %path2% and so on until the given root in --path. 
        Example: /var/files/records
            %path0% = /var/files/records
            %path1% = records
            %path2% = files
            %path3% = var
        With this option you can also replace %pathN% in keys or values and also
        in regular expressions.
        Use the --test flag to test and check the results!
        Note: Enabling this feature can change the behavior of existing 
        substitutions! This happen often e.g. when rename words which exists in 
        the name or replacement and also in the path you want to use or rename.

    --find|-f
        Optional; Find files in a semicolon seperated list of search keywords or 
        regular expressions (starting with "regex:". The list will be handled in
        OR conditons. The keyword must match with a part of the string in the 
        file location (path and filename).

    --recursive|-r
        Flag; Read all files under each directory starting from --path 
        recursively. 
        If a new configuration exists below this start directory it will be 
        ignored! Use --batch mode for this action.

    --keepcopy
        Flag; Keep existing file/s which will always add a '.1' suffix. When 
        running twice it will became '.1.1' as suffix.

    --hidden
        Include hidden (dot) files to be renamed

    --link
        soft|hard[;rel|abs]); Don't rename, create symlinks or hardlinks, 
        relativ or absolut to target. Values: soft|hard[;rel|abs]. If the second 
        parameter is not given relativ (rel) links will be created.

    --linkway
        Optional; Type of the link to be created: relative or absolut (rel|abs), 
        default: "rel". This will be used internally. If you use --link soft;rel
        the linkway will be extracted from that line.

    --history|-h
        Flag; This will enable the history/ for the moment ONLY the last action 
        with the option to undo it.

    --batch
        Not implemented yet. 
        Flag; Run the job recusivly from given --path as start 
        directory and start renaming. If a new configuration in the sub 
        directories exists it trys to load the configuration for the batch-mode 
        and execute it. This enables --recursiv and --history;

    --undo
        Flag; Revers/ undo the last action.

    --from-config
        Read saved configuration from given path and execute it.

    --set-config
        Flag; Sets, replaces existing configuration, and saves the parameters to
        a config file in the given path which adds a new folder ".multirename" 
        for later use with --from-config.

    --del-config
        Flag; Deletes the config from given --path.

    --show-config
        Flag; Shows the config parameter from a saved config to check or rebuild
        it. Use it with --from-config otherwise the current cmdlime comes back.

    --loglevel|--ll
        Logging level for the output of messages (0=Emerg ... 7=verbose/debug).
        For testing use 6 or 7; For cronjob/ batch etc. do not use lower than 5 
        to get important messages.

    --version|-v
        Flag; Show version informations

    --help|-h
        Show this help.



# Contributions

## Contributors are welcome!

Checkout a branch of master|testing|unstable including updates and switching
to one of it and including the externals with updates, use the helper script:

    ./helper/gitupdate.sh [branchname]

I decide to use staging areas to keep the "master" clean of bugs as good as 
possible and for maximum of stability.

Staging areas are "unstable" -> "testing" -> "master"

"master" should be always the latest stable release! (Incl. Hotfixes)

All new code/ development shoud go to "unstable". 
Also, if needed, to the externals which having also these staging areas.

If you would like to add features or push some improvements: The unstable branch 
is basicly the entry point and the latest code base.
Checkout the "unstable" branch create a new branch for your part to start in.
    
    git clone https://github.com/flobee/multirename.git
    cd multirename
    ./helper/gitupdate.sh unstable
    git checkout -b yourNewBranch

Note: You may also create own branches for the existing externals

    cd external/<name>
    git checkout -b yourNewBranch

If tests exists and the function of fixed bugs, new features is verified it will
be merged to "testing" (collecting the updates, versions, features)... for the 
next release candidate or sub releases.

Hotfixes will go to extra branches and will be merged directly to the "master".

When merging branches take a look into .gitmodules of _each_ branch and verify
that the branches still map to the branch name of the master project.
E.g.: The branch "testing" of the project maps to the "testing" branch of the
submodules eg. the library, and so on. 
For this reason you may use the helper script more often and follow this 
workflow: 

    # checkout given branch of the main project
    # init, updates and pulls the submodules for the given branch
    ./helper/gitupdate.sh [unstable|testing|master]

    # After a merge, e.g.:
    ./helper/gitupdate.sh testing
    git merge unstable
    # you need to check if it maps to testing and not unstable branches
    ls -al .gitmodules # which should link to the testing file


## Deployment

The build/make.php file will help to create files for the deployment e.g. if you 
would like to create new readme.md/wiki entrys or to create a release. All text 
files in the docs/ are involved. If you modify them there using markdown syntax 
the deployment and the creation with new documentation will be generated.

    # Will generate .md files for the wiki and the summary file /README.md
    php make.php deploy

    # will also create a new tar file with the release version in /deploy (the
    # version string must match to the current version of the multirename class)
    php make.php deploy '1.0.0-RC1'

Please use `make.php clean` after a deploy to not commit those files to the 
repository.

When commiting new stuff you should first commit the externals changes and at 
least the project version. The external commit ID should map to the commit ID of
the project when other people will check it out. I know this is not that handy 
but i have no other/better idea at the moment to handle it.



# License


                   GNU LESSER GENERAL PUBLIC LICENSE
                       Version 3, 29 June 2007

Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
Everyone is permitted to copy and distribute verbatim copies
of this license document, but changing it is not allowed.


  This version of the GNU Lesser General Public License incorporates
the terms and conditions of version 3 of the GNU General Public
License, supplemented by the additional permissions listed below.

  0. Additional Definitions.

  As used herein, "this License" refers to version 3 of the GNU Lesser
General Public License, and the "GNU GPL" refers to version 3 of the GNU
General Public License.

  "The Library" refers to a covered work governed by this License,
other than an Application or a Combined Work as defined below.

  An "Application" is any work that makes use of an interface provided
by the Library, but which is not otherwise based on the Library.
Defining a subclass of a class defined by the Library is deemed a mode
of using an interface provided by the Library.

  A "Combined Work" is a work produced by combining or linking an
Application with the Library.  The particular version of the Library
with which the Combined Work was made is also called the "Linked
Version".

  The "Minimal Corresponding Source" for a Combined Work means the
Corresponding Source for the Combined Work, excluding any source code
for portions of the Combined Work that, considered in isolation, are
based on the Application, and not on the Linked Version.

  The "Corresponding Application Code" for a Combined Work means the
object code and/or source code for the Application, including any data
and utility programs needed for reproducing the Combined Work from the
Application, but excluding the System Libraries of the Combined Work.

  1. Exception to Section 3 of the GNU GPL.

  You may convey a covered work under sections 3 and 4 of this License
without being bound by section 3 of the GNU GPL.

  2. Conveying Modified Versions.

  If you modify a copy of the Library, and, in your modifications, a
facility refers to a function or data to be supplied by an Application
that uses the facility (other than as an argument passed when the
facility is invoked), then you may convey a copy of the modified
version:

   a) under this License, provided that you make a good faith effort to
   ensure that, in the event an Application does not supply the
   function or data, the facility still operates, and performs
   whatever part of its purpose remains meaningful, or

   b) under the GNU GPL, with none of the additional permissions of
   this License applicable to that copy.

  3. Object Code Incorporating Material from Library Header Files.

  The object code form of an Application may incorporate material from
a header file that is part of the Library.  You may convey such object
code under terms of your choice, provided that, if the incorporated
material is not limited to numerical parameters, data structure
layouts and accessors, or small macros, inline functions and templates
(ten or fewer lines in length), you do both of the following:

   a) Give prominent notice with each copy of the object code that the
   Library is used in it and that the Library and its use are
   covered by this License.

   b) Accompany the object code with a copy of the GNU GPL and this license
   document.

  4. Combined Works.

  You may convey a Combined Work under terms of your choice that,
taken together, effectively do not restrict modification of the
portions of the Library contained in the Combined Work and reverse
engineering for debugging such modifications, if you also do each of
the following:

   a) Give prominent notice with each copy of the Combined Work that
   the Library is used in it and that the Library and its use are
   covered by this License.

   b) Accompany the Combined Work with a copy of the GNU GPL and this license
   document.

   c) For a Combined Work that displays copyright notices during
   execution, include the copyright notice for the Library among
   these notices, as well as a reference directing the user to the
   copies of the GNU GPL and this license document.

   d) Do one of the following:

       0) Convey the Minimal Corresponding Source under the terms of this
       License, and the Corresponding Application Code in a form
       suitable for, and under terms that permit, the user to
       recombine or relink the Application with a modified version of
       the Linked Version to produce a modified Combined Work, in the
       manner specified by section 6 of the GNU GPL for conveying
       Corresponding Source.

       1) Use a suitable shared library mechanism for linking with the
       Library.  A suitable mechanism is one that (a) uses at run time
       a copy of the Library already present on the user's computer
       system, and (b) will operate properly with a modified version
       of the Library that is interface-compatible with the Linked
       Version.

   e) Provide Installation Information, but only if you would otherwise
   be required to provide such information under section 6 of the
   GNU GPL, and only to the extent that such information is
   necessary to install and execute a modified version of the
   Combined Work produced by recombining or relinking the
   Application with a modified version of the Linked Version. (If
   you use option 4d0, the Installation Information must accompany
   the Minimal Corresponding Source and Corresponding Application
   Code. If you use option 4d1, you must provide the Installation
   Information in the manner specified by section 6 of the GNU GPL
   for conveying Corresponding Source.)

  5. Combined Libraries.

  You may place library facilities that are a work based on the
Library side by side in a single library together with other library
facilities that are not Applications and are not covered by this
License, and convey such a combined library under terms of your
choice, if you do both of the following:

   a) Accompany the combined library with a copy of the same work based
   on the Library, uncombined with any other library facilities,
   conveyed under the terms of this License.

   b) Give prominent notice with the combined library that part of it
   is a work based on the Library, and explaining where to find the
   accompanying uncombined form of the same work.

  6. Revised Versions of the GNU Lesser General Public License.

  The Free Software Foundation may publish revised and/or new versions
of the GNU Lesser General Public License from time to time. Such new
versions will be similar in spirit to the present version, but may
differ in detail to address new problems or concerns.

  Each version is given a distinguishing version number. If the
Library as you received it specifies that a certain numbered version
of the GNU Lesser General Public License "or any later version"
applies to it, you have the option of following the terms and
conditions either of that published version or of any later version
published by the Free Software Foundation. If the Library as you
received it does not specify a version number of the GNU Lesser
General Public License, you may choose any version of the GNU Lesser
General Public License ever published by the Free Software Foundation.

  If the Library as you received it specifies that a proxy can decide
whether future versions of the GNU Lesser General Public License shall
apply, that proxy's public statement of acceptance of any version is
permanent authorization for you to choose that version for the
Library.


# Authors

## Core developers

Florian Blasel


# Bugs

There are one or some and hopefully none! This is alpha version! After writing 
the tests (done) some has been removed. 
Be sure using the --test mode and check all results! Have a look at the output 
when substitution or search keywards having special characters e.g: ? & ... 
I think the pcre engine does not like it but i haven't checked it yet.

Your help would be great to find bugs or add features and improvements.


# About / History

The program is made for users which have not that detailed knowlege using the
shell. Also me :-) but i know php and find my solution to help myself for a
solution to rename files like i need it. Multirename was born.
Nothing new! And maybe already done anywhere in any rename program. 
Maybe some of my ideas will find useful, finds a new home ... Hopfully it will 
stay here :-)
The very beginning of this program was in ~2002 and now, again because of music
and video files the vdr (video disk recording) project gave me the idea to
finish this program including some features i was looking for.


