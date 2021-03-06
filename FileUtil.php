<?php
/* -*- tab-width: 4; indent-tabs-mode: nil; c-basic-offset: 4 -*- */
/*
# ***** BEGIN LICENSE BLOCK *****
# This file is part of InDefero, an open source project management application.
# Copyright (C) 2010 Céondo Ltd and contributors.
#
# InDefero is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# InDefero is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
#
# ***** END LICENSE BLOCK ***** */

/**
 * File utilities.
 *
 */
class Gatuf_FileUtil {
    /**
     * Extension supported by the syntax highlighter.
     */
    public static $supportedExtenstions = array(
              'ascx', 'ashx', 'asmx', 'aspx', 'browser', 'bsh', 'c', 'cl', 'cc',
              'config', 'cpp', 'cs', 'csh', 'csproj', 'css', 'cv', 'cyc', 'el', 'fs',
              'h', 'hh', 'hpp', 'hs', 'html', 'html', 'java', 'js', 'lisp', 'master',
              'pas', 'perl', 'php', 'pl', 'pm', 'py', 'rb', 'scm', 'sh', 'sitemap',
              'skin', 'sln', 'svc', 'vala', 'vb', 'vbproj', 'vbs', 'wsdl', 'xhtml',
              'xml', 'xsd', 'xsl', 'xslt');

    /**
     * Test if an extension is supported by the syntax highlighter.
     *
     * @param string The extension to test
     * @return bool
     */
    public static function isSupportedExtension($extension) {
        return in_array($extension, self::$supportedExtenstions);
    }

    /**
     * Returns a HTML snippet with a line-by-line pre-rendered table
     * for the given source content
     *
     * @param array file information as returned by getMimeType or getMimeTypeFromContent
     * @param string the content of the file
     * @return string
     */
    public static function highLight($fileinfo, $content) {
        $pretty = '';
        if (self::isSupportedExtension($fileinfo[2])) {
            $pretty = ' prettyprint';
        }
        $table = array();
        $i = 1;
        foreach (preg_split("/\015\012|\015|\012/", $content) as $line) {
            $table[] = '<tr class="c-line"><td class="code-lc" id="L'.$i.'"><a href="#L'.$i.'">'.$i.'</a></td>'
                .'<td class="code mono'.$pretty.'">'.IDF_Diff::padLine(Gatuf_esc($line)).'</td></tr>';
            $i++;
        }
        return Gatuf_Template::markSafe(implode("\n", $table));
    }

    /**
     * Find the mime type of a file.
     *
     * Use /etc/mime.types to find the type.
     *
     * @param string Filename/Filepath
     * @param array  Mime type found or 'application/octet-stream', basename, extension
     */
    public static function getMimeType($file) {
        static $mimes = null;
        if ($mimes == null) {
            $mimes = array();
            $src = Gatuf::config('idf_mimetypes_db', '/etc/mime.types');
            $filecontent = @file_get_contents($src);
            if ($filecontent !== false) {
                $mimes = preg_split("/\015\012|\015|\012/", $filecontent);
            }
        }

        $info = pathinfo($file);
        if (isset($info['extension'])) {
            foreach ($mimes as $mime) {
                if ('#' != substr($mime, 0, 1)) {
                    $elts = preg_split('/ |\t/', $mime, -1, PREG_SPLIT_NO_EMPTY);
                    if (in_array($info['extension'], $elts)) {
                        return array($elts[0], $info['basename'], $info['extension']);
                    }
                }
            }
        } else {
            // we consider that if no extension and base name is all
            // uppercase, then we have a text file.
            if ($info['basename'] == strtoupper($info['basename'])) {
                return array('text/plain', $info['basename'], 'txt');
            }
            $info['extension'] = 'bin';
        }
        return array('application/octet-stream', $info['basename'], $info['extension']);
    }

    /**
     * Find the mime type of a file using the fileinfo class.
     *
     * @param string Filename/Filepath
     * @param string File content
     * @return array Mime type found or 'application/octet-stream', basename, extension
     */
    public static function getMimeTypeFromContent($file, $filedata) {
        $info = pathinfo($file);
        $res = array('application/octet-stream',
                     $info['basename'],
                     isset($info['extension']) ? $info['extension'] : 'bin');
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mime = finfo_buffer($finfo, $filedata);
            finfo_close($finfo);
            if ($mime) {
                $res[0] = $mime;
            }
            if (!isset($info['extension']) && $mime) {
                $res[2] = (0 === strpos($mime, 'text/')) ? 'txt' : 'bin';
            } elseif (!isset($info['extension'])) {
                $res[2] = 'bin';
            }
        }
        return $res;
    }

    /**
     * Find if a given mime type is a text file.
     * This uses the output of the self::getMimeType function.
     *
     * @param array (Mime type, file name, extension)
     * @return bool Is text
     */
    public static function isText($fileinfo) {
        if (0 === strpos($fileinfo[0], 'text/')) {
            return true;
        }
        $ext = 'mdtext php-dist h gitignore diff patch';
        $extra_ext = trim(Config::config('idf_extra_text_ext', ''));
        if (!empty($extra_ext))
            $ext .= ' ' . $extra_ext;
        $ext = array_merge(self::$supportedExtenstions, explode(' ' , $ext));
        return (in_array($fileinfo[2], $ext));
    }
}
