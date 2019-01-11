# Passw0rd SDK PHP
[![Build Status](https://travis-ci.com/passw0rd/sdk-php.png?branch=master)](https://travis-ci.com/passw0rd/sdk-php)
[![GitHub license](https://img.shields.io/badge/license-BSD%203--Clause-blue.svg)](https://github.com/VirgilSecurity/virgil/blob/master/LICENSE)


[Introduction](#introduction) | [Features](#features) | [Register Your Account](#register-your-account) | [Install and configure SDK](#install-and-configure-sdk) | [Prepare Your Database](#prepare-your-database) | [Usage Examples](#usage-examples) | [Docs](#docs) | [Support](#support)

## Introduction
<a href="https://passw0rd.io/"><img width="260px" src="https://cdn.virgilsecurity.com/assets/images/github/logos/passw0rd.png" align="left" hspace="0" vspace="0"></a>[Virgil Security](https://virgilsecurity.com) introduces an implementation of the [Password-Hardened Encryption (PHE) protocol](https://virgilsecurity.com/wp-content/uploads/2018/11/PHE-Whitepaper-2018.pdf) that provides developers with a technology to protect users passwords from offline/online attacks and make stolen passwords useless even if your database has been compromised.

PHE is a new, more secure mechanism that protects user passwords and lessens the security risks associated with weak passwords. Neither Virgil nor attackers know anything about user's password.

**Authors of the PHE protocol**: Russell W. F. Lai, Christoph Egger, Manuel Reinert, Sherman S. M. Chow, Matteo Maffei and Dominique Schroder.

## Features
- Zero knowledge of user password
- Protection from online attacks
- Protection from offline attacks
- Instant invalidation of stolen database
- User data encryption with a personal key


## Register Your Account
Before starting practicing with the SDK and usage examples be sure that:
- you have a registered passw0rd Account
- you created passw0rd Application
- and you got your passw0rd application's credentials, such as: Application Access Token, Service Public Key, Client Secret Key

If you don't have an account or a passw0rd project with its credentials, please use the [passw0rd CLI](https://github.com/passw0rd/cli) to get it.


## Install and Configure SDK
The passw0rd PHP SDK is provided as a package named `passw0rd/sdk`. The package is distributed via Composer. The 
package
 is available for PHP 7.0 or newer.


### Install SDK Package
Install passw0rd SDK library with the following code:
```bash
composer require passw0rd/sdk
```


### Configure SDK
Here is an example of how to specify your credentials SDK class instance:
```php
use Dotenv\Dotenv;
use passw0rd\Protocol\Protocol;
use passw0rd\Protocol\ProtocolContext;

(new Dotenv("../"))->load(); // Add this string to index file. Load .env variables (required string). 

$context = (new ProtocolContext)->create([
    'accessToken' => $_ENV['ACCESS_TOKEN'],
    'publicKey' => $_ENV['PUBLIC_KEY'],
    'secretKey' => $_ENV['SECRET_KEY'],
    'updateToken' => $_ENV['UPDATE_TOKEN'],
]);

try {
    $protocol = new Protocol($context);
}
catch(\Exception $e) {
    var_dump($e->getMessage());
}
```

## Prepare Your Database
Passw0rd SDK allows you to easily perform all the necessary operations to create, verify and rotate user password without requiring any additional actions.

In order to create and work with user's protected passw0rd you have to set up your database with an additional column.

The column must have the following parameters:
<table class="params">
<thead>
		<tr>
			<th>Parameters</th>
			<th>Type</th>
			<th>Size (bytes)</th>
			<th>Description</th>
		</tr>
</thead>

<tbody>
<tr>
	<td>passw0rd_record</td>
	<td>bytearray</td>
	<td>210</td>
	<td> A unique record, namely a user's protected passw0rd.</td>
</tr>

</tbody>
</table>


## Usage Examples

### Enroll User passw0rd

Use this flow to create a new passw0rd record for a user in your DB.

> Remember, if you already have a database with user passwords, you don't have to wait until a user logs in to your system to implement Passw0rd. You can go through your database and enroll user passw0rd at any time.

So, in order to create passw0rd for a new database or an available one, go through the following operations:
- Take user's **password** (or its hash or whatever you use) and pass it into the `enroll` method in SDK on your 
Server side.
- Passw0rd SDK will send a request to passw0rd service to get enrollment.
- Then, passw0rd SDK will create user's passw0rd **record**. You need to store this unique user's `record` (recordBytes or recordBase64 format) in your database in an associated column.

```php
use Dotenv\Dotenv;
use passw0rd\Protocol\Protocol;
use passw0rd\Protocol\ProtocolContext;

(new Dotenv("../"))->load(); // Load .env variables (required string)

$context = (new ProtocolContext)->create([
    'appToken' => $_ENV['APP_TOKEN'],
    'servicePublicKey' => $_ENV['SERVICE_PUBLIC_KEY'],
    'appSecretKey' => $_ENV['APP_SECRET_KEY'],
    'updateToken' => $_ENV['UPDATE_TOKEN'],
]);

try {
    $protocol = new Protocol($context);
    $enroll = $protocol->enroll($password)); // password_record
}
catch(\Exception $e) {
    var_dump($e->getMessage());
    die;
}
```

When you've created a `passw0rd_record` for all users in your DB, you can delete the unnecessary column where user passwords were previously stored.


### Verify User passw0rd

Use this flow at the "sign in" step when a user already has his or her own unique `record` in your database. This function allows you to verify that the password that the user has passed is correct. 
You have to pass his or her `record` from your DB into the `VerifyPassword` function:

```php
use Dotenv\Dotenv;
use passw0rd\Protocol\Protocol;
use passw0rd\Protocol\ProtocolContext;

(new Dotenv("../"))->load(); // Load .env variables (required string)

$context = (new ProtocolContext)->create([
    'appToken' => $_ENV['APP_TOKEN'],
    'servicePublicKey' => $_ENV['SERVICE_PUBLIC_KEY'],
    'appSecretKey' => $_ENV['APP_SECRET_KEY'],
    'updateToken' => $_ENV['UPDATE_TOKEN'],
]);

try {
    $protocol = new Protocol($context);
    $verifyPassword = $protocol->verifyPassword($password, $record));
}
catch(\Exception $e) {
    var_dump($e->getMessage());
    die;
}
```

### Rotate User passw0rd

This function allows you to use a special `UpdateTokens` to update users' `record` in your database.

> Use this flow only if your database has been COMPROMISED!
When a user just needs to change his or her own password, use the `enroll` function to replace old user's `passw0rd_record` value in your DB with a new user's `passw0rd_record`.

How it works:
- Get your `UpdateToken` using [Passw0rd CLI](https://github.com/passw0rd/cli).
- Specify the `UpdateToken` in the Passw0rd SDK on your Server side.
- Then use the `Update` records function to create new user's `record` for your users (you don't need to ask your users to create a new password).
- Finally, save the new user's `record` into your database.

Here is an example of using the `Update` records function:
```php
use Dotenv\Dotenv;
use passw0rd\Protocol\Protocol;
use passw0rd\Protocol\ProtocolContext;

(new Dotenv("../"))->load(); // Load .env variables (required string)

$context = (new ProtocolContext)->create([
    'appToken' => $_ENV['APP_TOKEN'],
    'servicePublicKey' => $_ENV['SERVICE_PUBLIC_KEY'],
    'appSecretKey' => $_ENV['APP_SECRET_KEY'],
    'updateToken' => $_ENV['UPDATE_TOKEN'],
]);

try {
    $protocol = new Protocol($context);
    $updatedRecord = $protocol->updatePassword($record));
}
catch(\Exception $e) {
    var_dump($e->getMessage());
    die;
}
```


## Docs
* [Passw0rd][_passw0rd] home page
* [The PHE WhitePaper](https://virgilsecurity.com/wp-content/uploads/2018/11/PHE-Whitepaper-2018.pdf) - foundation principles of the protocol

## License

This library is released under the [3-clause BSD License](LICENSE.md).

## Support
Our developer support team is here to help you. Find out more information on our [Help Center](https://help.virgilsecurity.com/).

Also, get extra help from our support team: support@VirgilSecurity.com.

[_passw0rd]: https://passw0rd.io/
