<?php

/**
 * Copyright (C) 2017  Oliver Maerz - http://olivermaerz.com/contact/
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301  USA
 *
 *
 * ZenbershipAuth module for SimpleSAMLphp to use Zenbership members db
 *
 * @category   AuthModule
 * @package    ZenbershipAuth
 * @author     Oliver Maerz
 * @copyright  2017 Oliver Maerz
 * @license    http://www.gnu.org/licenses/gpl-3.0.en.html
 * @version    0.1
 * @link       https://olivermaerz.com/single-sign-on-use-zenbership-as-a-saml-idp/
 *
 */
class sspmod_wordpressauth_Auth_Source_ZenbershipAuth extends sspmod_core_Auth_UserPassBase {


    /* The database DSN */
    private $dsn;

    /* The database username & password. */
    private $username;
    private $password;

    /* Table name for users tables (usually wp_users) */
    private $userstable;

    public function __construct($info, $config) {
        parent::__construct($info, $config);

        /* Load DSN, username, password, userstable and zenbership_salt from configuration */
        if (!is_string($config['dsn'])) {
            throw new Exception('Missing or invalid dsn option in config.');
        }
        $this->dsn = $config['dsn'];

        if (!is_string($config['username'])) {
            throw new Exception('Missing or invalid username option in config.');
        }
        $this->username = $config['username'];

        if (!is_string($config['password'])) {
            throw new Exception('Missing or invalid password option in config.');
        }
        $this->password = $config['password'];

        if (!is_string($config['members_table'])) {
            throw new Exception('Missing or invalid members_table option in config.');
        }
        $this->members_table = $config['members_table'];

        if (!is_string($config['members_data_table'])) {
            throw new Exception('Missing or invalid members_data_table option in config.');
        }
        $this->members_data_table = $config['members_data_table'];

        if (!is_string($config['zenbership_salt'])) {
            throw new Exception('Missing or invalid zenbership_salt option in config.');
        }
        $this->zenbership_salt = $config['zenbership_salt'];
    }

    protected function login($username, $password) {
        /* Connect to the database. */
        $db = new PDO($this->dsn, $this->username, $this->password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        /* Ensure that we are operating with UTF-8 encoding. */
        $db->exec("SET NAMES 'utf8'");

        /* Prepare statement (PDO) */
        $st = $db->prepare('SELECT username, password, salt, email, first_name, last_name FROM ' . $this->members_table . ' LEFT JOIN ' . $this->members_data_table . ' ON id=member_id WHERE username = :username');

        if (!$st->execute(array('username' => $username))) {
            throw new Exception("Failed to query database for user.");
        }

        /* Retrieve the row from the database. */
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            /* User not found. */
            throw new SimpleSAML_Error_Error('WRONGUSERPASS');
        }

        /* Load the Portable PHP password hashing framework */
        //require_once( dirname(__FILE__).'/../../../vendor/PasswordHash.php' );
        //$hasher = new PasswordHash(8, TRUE);


        /* Check the hashed password against the hash in Zenbership's members table */
        $hashed_submitted_password = sha1(md5(md5($password) . md5($row['salt']) . md5($this->zenbership_salt)));
        if ($hashed_submitted_password != $row['password']){
            /* Invalid password. */
            throw new SimpleSAML_Error_Error('WRONGUSERPASS');
        }

        /* Create the attribute array of the user. */
        $attributes = array(
            'uid' => array($username),
            'username' => array($username),
            'name' => array($row['first_name'] . ' ' . $row['last_name']),
            'displayName' => array($row['first_name'] . ' ' . $row['last_name']),
            'email' => array($row['email']),
            // TODO: get memberships from DB for eduPersonAffiliation - instead of hardcoding it
            'eduPersonAffiliation' => array('member', 'participant'),
        );

        /* Return the attributes. */
        return $attributes;
    }
}
