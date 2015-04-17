<?php
/**
 * Please, do not edit this file. The configuration file for Debian system
 * is located at /etc/phpmyadmin directory.
 */

// Load secret generated on postinst
include('/etc/phpmyadmin/blowfish_secret.inc.php');

// Load autoconf local config
include('config/config.inc.php');

// Load user's local config
include('/etc/phpmyadmin/config.inc.php');

// Set the default server if there is no defined
if (!isset($cfg['Servers'])) {
    $cfg['Servers'][1]['host'] = 'localhost';
}

// Set the default values for $cfg['Servers'] entries
for ($i=1; (!empty($cfg['Servers'][$i]['host']) || (isset($cfg['Servers'][$i]['connect_type']) && $cfg['Servers'][$i]['connect_type'] == 'socket')); $i++) {
    if (!isset($cfg['Servers'][$i]['host'])) {
        $cfg['Servers'][$i]['host'] = '';
    }
    if (!isset($cfg['Servers'][$i]['port'])) {
        $cfg['Servers'][$i]['port'] = '';
    }
    if (!isset($cfg['Servers'][$i]['socket'])) {
        $cfg['Servers'][$i]['socket'] = '';
    }
    if (!isset($cfg['Servers'][$i]['connect_type'])) {
        $cfg['Servers'][$i]['connect_type'] = 'tcp';
    }
    if (!isset($cfg['Servers'][$i]['extension'])) {
        $cfg['Servers'][$i]['extension'] = 'mysql';
    }
    if (!isset($cfg['Servers'][$i]['compress'])) {
        $cfg['Servers'][$i]['compress'] = FALSE;
    }
    if (!isset($cfg['Servers'][$i]['controluser'])) {
        $cfg['Servers'][$i]['controluser'] = '';
    }
    if (!isset($cfg['Servers'][$i]['controlpass'])) {
        $cfg['Servers'][$i]['controlpass'] = '';
    }
    if (!isset($cfg['Servers'][$i]['auth_type'])) {
        $cfg['Servers'][$i]['auth_type']  = 'cookie';
    }
    if (!isset($cfg['Servers'][$i]['user'])) {
        $cfg['Servers'][$i]['user'] = 'root';
    }
    if (!isset($cfg['Servers'][$i]['password'])) {
        $cfg['Servers'][$i]['password'] = '';
    }
    if (!isset($cfg['Servers'][$i]['only_db'])) {
        $cfg['Servers'][$i]['only_db'] = '';
    }
    if (!isset($cfg['Servers'][$i]['verbose'])) {
        $cfg['Servers'][$i]['verbose'] = '';
    }
    if (!isset($cfg['Servers'][$i]['pmadb'])) {
        $cfg['Servers'][$i]['pmadb'] = '';
    }
    if (!isset($cfg['Servers'][$i]['bookmarktable'])) {
        $cfg['Servers'][$i]['bookmarktable'] = '';
    }
    if (!isset($cfg['Servers'][$i]['relation'])) {
        $cfg['Servers'][$i]['relation'] = '';
    }
    if (!isset($cfg['Servers'][$i]['table_info'])) {
        $cfg['Servers'][$i]['table_info'] = '';
    }
    if (!isset($cfg['Servers'][$i]['table_coords'])) {
        $cfg['Servers'][$i]['table_coords'] = '';
    }
    if (!isset($cfg['Servers'][$i]['pdf_pages'])) {
        $cfg['Servers'][$i]['pdf_pages'] = '';
    }
    if (!isset($cfg['Servers'][$i]['column_info'])) {
        $cfg['Servers'][$i]['column_info'] = '';
    }
    if (!isset($cfg['Servers'][$i]['history'])) {
        $cfg['Servers'][$i]['history'] = '';
    }
    if (!isset($cfg['Servers'][$i]['verbose_check'])) {
        $cfg['Servers'][$i]['verbose_check'] = TRUE;
    }
    if (!isset($cfg['Servers'][$i]['AllowRoot'])) {
        $cfg['Servers'][$i]['AllowRoot'] = TRUE;
    }
    if (!isset($cfg['Servers'][$i]['AllowDeny'])) {
        $cfg['Servers'][$i]['AllowDeny'] = array ('order' => '',
                                                  'rules' => array());
    }
}

?>
