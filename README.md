## Iconet integration into ExampleNetA

Check [setup.me](setup.md) for Setup Instructions.

This repository is part of the iconet prototype development, where two networks are linked through the iconet
mechanisms.
This is a basic network, with a low feature set and the code uses no frameworks. This allows for a quite untangled
demonstration, on how to integrate the iconet technology.
ExampleNetA is taken from: https://github.com/yaswanthpalaghat/Social-Network-using-php-and-mysql

## A: Development Doku

Here we document the development from ExampleNetA towards interconnectivity.

#### Files added:

While not necessary, for demonstration purposes we intend to split the initial structure of ExampleNetA as much as
possible from the required structure added for iconet.

##### iconet/iconet.sql

Fills the iconet database with the required tables. (execute only on setup)

##### iconet/index.php

Catches incoming requests, initiates required action.

##### iconet/api_outwards
HTTP helper to send packages to URLS. 
##### iconet/database
Getters and Setters for the iconet DB
##### iconet/formats
Functions to check variables if their format adheres to iconet structures
##### iconet/request_builder
Translates internally required variables into iconet requests. (potentially merges later with api_outwards)
##### iconet/cryptography
Handles iconet encryption and decryption
##### iconet/libs/AES
AES class for symetric encryption

### Features added:

Internal process documentation for the incrementally added features.

#### Feature: Separate Infrastructure

Description:

Add a separate database and infrastructure for all iconet related aspects.
While networks might want the iconet features to be rather integrated, for showcasing this is ideal.

Code added:

iconet database table generation, folders for iconet handlers.

#### Feature: Contactlist

Description:

In its initial feature-set, Users of ExampleNetA have no ability to display or manage contacts/friends. So far the
friends feature of ExampleNetA only enables adding and removing off friends, where being friends entails that posts are
displayed within the friends feed.
While iconet does not set any requirements on how contacts/friends are handled by a network, there needs to be some way
for a user to add and manage external addresses and hold them in a contact-list, so they then may send messages towards
them.

Code added:

Since ExampleNetA has no such feature, we add it. We change the requests.php to contacts.php, which previously only
displayed pending friend requests.
A user will be able to display all their internal friends and friends-requests, as well as their external contacts.


#### Feature: Iconet-Address generation
Description:

Each user, to be able to be addressed by users of external networks, needs some global address.
This will be the local identifier of a user and the global address of the network, written like localID@globalURL

Code added:

Assume, ExampleNetA holds the global URL ExampleNetA.net
On User registration, a global Address is automatically generated and added to the database.
This address will be displayed on the profile of each user.

#### Feature: Basic S2S: Request and Return PublicKey of Users.

Description:

Required to provided external sources with pubkey of local users.

Code added:

Generate internal Server2Server handling structure. For now use trivial placeholders for external s2s communication.
Return Pubkey on request.

    For packages use JSON
    Request:
    {
    "type": "Request PublicKey"
    "address": %address
    }
    Response:
    {
    "type": "Response PublicKey"
    "address": %address
    "publickey": %pubkey
    }

#### Feature : Encryption Basics
Description:

Create cryptography interface, import AES and openSSL. 

Code added:

Imported AES and openSSL, created cryptography.php
Implement and test hybrid encryption process.

#### Feature: Encryption Adaption
Description:

Notifications and content are encrypted via AES, the common Secret is encrypted via GPG with an in openSSL generated public key. A private key for decryption is generated simultaneously.

Code added:

Created Keys on User Registration & Store in DB
Created Key format in formats.php
Use Checks of Format on Receiving Keys.
Prepare: Encrypt Outgoing Packages, Decrypt Incoming Packages. (No such packages are being sent yet)

#### Feature : Processing
Description:

Create processing interface with iconet API. Define non-optional elements of processed packages. The packages MUST all contain the element "type" and respectively:
    "Request Publickey": 
        "address" - address of requested public key's owner
"Send Notification":
        "sender" - address of sender
        "predata" - encrypted preview data of content
        "cipher" - with public key encrypted secret
        "to" - address of the notification's receiver
    "Request Content":
        "id" - identification number of notification
        "address" - address of sender
    "Request Format":
        "name" - name of requested format: "post-comments"
    "Send Interaction":
        "id" - identification number of notification
        "sender" - address of sender
        "interaction" - content of interaction
    "Send Publickey":
        "address" - address of requested public key's owner
        "publickey" - requested public key
    "Send Content":
        "sender" - address of sender
        "format" - name of requested format: "post-comments"
        "content" - sent content
    "Send Format":
        "name" - name of requested format: "post-comments"
        "format" - defined format for "post-comments"

Code added:

Created package_builder.php, package_handler.php, index.php, post_office.php, processor.php.
Added folder posts for saving content via ID.
Implement and test processing.
