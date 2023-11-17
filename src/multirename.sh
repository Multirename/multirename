#!/bin/sh

# {{{ Multirename
# for MUMSYS Library for Multi User Management System
# -----------------------------------------------------------------------------
# @license LGPL Version 3 http://www.gnu.org/licenses/lgpl-3.0.txt
# @copyright (c) 2015 by Florian Blasel
# @author Florian Blasel <flobee.code@gmail.com>
# -----------------------------------------------------------------------------
# @category    Mumsys
# @package     Library
# @subpackage  Multirename
# @version     2.4.6
# Created on 2015-04-08
# }}}

_DIR=$(dirname "$0")

# Helper script. 
# On some systems open_basedir restrictions take affect. Eg. on synology
# systems or systems without root access.
# In cli mode you may can work around like this:

php -d open_basedir=Off "$_DIR"/multirename.php $*
