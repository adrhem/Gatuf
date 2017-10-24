<?php
/* -*- tab-width: 4; indent-tabs-mode: nil; c-basic-offset: 4 -*- */
/*
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Plume Framework, a simple PHP Application Framework.
# Copyright (C) 2001-2007 Loic d'Anterroches and contributors.
#
# Plume Framework is free software; you can redistribute it and/or modify
# it under the terms of the GNU Lesser General Public License as published by
# the Free Software Foundation; either version 2.1 of the License, or
# (at your option) any later version.
#
# Plume Framework is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Lesser General Public License for more details.
#
# You should have received a copy of the GNU Lesser General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
#
# ***** END LICENSE BLOCK ***** */

/**
 * Migration script.
 */
set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__));
require 'Gatuf.php';

function usage()
{
    echo 'Usage examples:'."\n"
        .' Extract all:      extracttemplates.php path/to/config.php path/to/outpudir'."\n";

}
function debug($what)
{
    global $debug;
    if ($debug) {
        echo($what."\n");
    }
}

if ($argc !== 3) {
    usage();
    die();
}
$conf = $argv[1];
$outputdir = $argv[2];
Gatuf::start($conf);
$generator = new Gatuf_Translation_Generator();
$generator->generate($outputdir);
echo 'Done', "\n";
