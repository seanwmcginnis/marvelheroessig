<?php

/* Convenience function for loading constants from php.ini.  The following are expected:
 * myapp.cfg.DB_HOST: THe mysql host IP address
 * myapp.cfg..DB_USER: The mysql username
 * myapp.cfg.DB_PASS: THe mysql username
 */
// Very simple loader
function loadConfig( $vars = array() ) {
    foreach( $vars as $v ) {
        define( $v, get_cfg_var( "myapp.cfg.$v" ) );
    }
}
 
// Then call :
$cfg = array( 'DB_HOST', 'DB_USER', 'DB_PASS' );
loadConfig( $cfg );

?>