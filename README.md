# ZenbershipAuth
Authentication module for SimpleSAMLphp to use Zenbership's member database. Allows using Zenbership as a SAML 2.0 Identity Provider. Written for MySQL/MariaDB.

## Setup

### 1 Setup Zenbership

### 2 Install SimpleSAMLphp 

Install SimpleSAMLphp and follow the SimpleSAMLphp instructions to set it up as an identity provider ([SimpleSAMLphp Identity Provider QuickStart](https://simplesamlphp.org/docs/stable/simplesamlphp-idp)) 

### 3 Add ZenbershipAuth Module

Create new directory `zenbershipauth` under the `modules` directory (`simplesaml/modules/zenbershipauth`) and copy the files from this repository to it. 

### 4 Configure Authentication Source 

Edit the configuration file for authentication sources `simplesaml/config/authsources.php` and add:

```php

'zenbershipauthinstance' => array(
    'dsn' => 'mysql:host=localhost;port=3306;dbname=<mysql database name>',
    'username' => '<mysql username>',
    'password' => '<mysql password>',
     'members_table' => 'ppSD_members',
     'members_data_table' => 'ppSD_member_data',
     // Get the salt from the file <zenbership-home>/admin/sd-system/salt.php
     'zenbership_salt'  => '591e65dc356a9714ea912b39a096d28fac391badbe28185c6885e048014a79eec4f65a0a699d8',
     'zenbershipauth:ZenbershipAuth'
),
 
```
Replace the placeholders with your MySQL host, username, password and database name etc. 

### 5 Set Authentication Source in Metadata File

Edit the metadata file for the hosted SAML 2.0 IdP `simplesaml/metadata/saml20-idp-hosted.php`
and set `wpauthinstance` as your authentication source: 

```php

/*
 * Authentication source to use. Must be one that is configured in
 * 'config/authsources.php'.
 */
'auth' => 'zenbershipauthinstance',
 
```
