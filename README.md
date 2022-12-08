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
HTTP helper to send packets to URLS. 
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

#### **Feature:** Separate Infrastructure

**Description:**
Add a separate database and infrastructure for all iconet related aspects.
While networks might want the iconet features to be rather integrated, for showcasing this is ideal.

**Code added:**
iconet database table generation, folders for iconet handlers.

#### **Feature:** Contactlist

**Description:**
In its initial feature-set, Users of ExampleNetA have no ability to display or manage contacts/friends. So far, the
friends feature of ExampleNetA only enables adding and removing friends, where being friends entails that posts are
displayed on the friends feed.
While iconet does not set any requirements on how contacts/friends are handled by a network, there needs to be some way
for a user to add and manage external addresses and hold them in a contact-list, so they can send messages to
them.

**Code added:**
Since ExampleNetA has no such feature, we add it. We change the requests.php to contacts.php, which previously only
displayed pending friend requests.
A user will be able to display all their internal friends and friends-requests, as well as their external contacts.


#### **Feature:** Iconet-Address generation
**Description:**
Each user, to be able to be addressed by users of external networks, needs some global address.
This will be the local identifier of a user and the global address of the network, written like localID@globalURL

**Code added:**
Assume, ExampleNetA holds the global URL ExampleNetA.net
On User registration, a global Address is automatically generated and added to the database.
This address will be displayed on the profile of each user.

#### **Feature: Basic S2S:** Request and Return PublicKey of Users.

**Description:**
Required to provided external sources with pubkey of local users.

**Code added:**
Generate internal Server2Server handling structure. For now use trivial placeholders for external s2s communication.
Return Pubkey on request.\
Packets are formatted in JSON.

Request:
```json
{
    "type": "Request PublicKey",
    "address": %address
}
```
Response:

```json
{
    "type": "PublicKeyResponse",
    "address": %address,
    "publickey": %pubkey
}
```

#### **Feature:** Encryption Basics

**Description:**
Create cryptography interface, import AES and openSSL. 

**Code added:**
Imported AES and openSSL, created cryptography.php
Implement and test hybrid encryption process.

#### **Feature:** Encryption Adaption
**Description:**
Notifications and content are encrypted via AES, the common Secret is encrypted via GPG with an in openSSL generated public key. A private key for decryption is generated simultaneously.

**Code added:**
Created Keys on User Registration & Store in DB
Created Key format in formats.php
Use Checks of Format on Receiving Keys.
Prepare: Encrypt outgoing packets, eecrypt incoming packets. (No such packets are being sent yet)

#### **Feature:** Processing

Create processing interface with iconet API. Define non-optional elements of processed packets. In this table these are
written in bold lettering.

| **Packet**              | **Field**                      | **Value/Example**                     | **Description**                                                                                                                                        |
|-------------------------|--------------------------------|---------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------|
|                         |                                |                                       |                                                                                                                                                        |
| **Public Key Request**  | **`type`**                     | `"publicKeyRequest"`                  | **Required:** The packet type is a specific name, identifying, what kind of packet is sent.                                                            |
|                         | **`address`**                  | `"bob@bobnet.org"`                    | **Required:** "Address" is the global address of the owner of the requested public key including the owners name and the one of the hosting network.   |
|                         |                                |                                       |                                                                                                                                                        |
| **Public Key Response** | **`type`**                     | `"publicKeyResponse"`                 | **Required**                                                                                                                                           |
|                         | **`address`**                  | `"bob@bobnet.org"`                    | **Required**                                                                                                                                           |
|                         | **`publicKey`**                | `"-----BEGIN PUBLIC KEY-----\nMI..."` | **Required:** The requested public key is sent as string.                                                                                              |
|                         |                                |                                       |                                                                                                                                                        |
| **Notification**        | **`type`**                     | `"Notification"`                      | **Required**                                                                                                                                           |
|                         | **`actor`**                    | `"alice@alicenet.net"`                | **Required:** "Actor" specifies the address of the sender of the content.                                                                              |
|                         | **`to`**                       | `"bob@bobnet.org"`                    | **Required:** "To" contains the address of the person, the content is sent to.                                                                         |
|                         | **`encryptedSecret`**          | `"jtqgp5D2Z4..."`                     | **Required:** The asymmetrically encrypted key for symmetric encryption is named "encryptedSecret".                                                    |
|                         | **`predata`**                  |                                       | **Required:** This array includes the symmetrically encrypted pre-data and the content identifier.                                                     |
|                         | **`predata.id`**               | `"92defee110..."`                     | **Required:** The content identifier is necessary, to be able to assign the pre-data to the actual content.                                            |
|                         | **`predata.subject`**          | `"New message from ..."`              | **Required:** The subject is a string with a short description of the received content.                                                                |
|                         | `encrypted`                    | `bool(true)`                          | "Encrypted" is true, if the pre-data is encrypted.                                                                                                     |
|                         | `interoperability`             |                                       | Here, interoperability information about network of sender can be attached. This helps processing content of foreign technologies.                     |
|                         | `interoperability.protocol`    | `"ExampleNetA"`                       | If needed, the technology of the sender's network can be described.                                                                                    |
|                         | `interoperability.contentType` | `"Posting"`                           | The content type tells the receiving network, what sort of content is sent.                                                                            |
|                         |                                |                                       |                                                                                                                                                        |
| **Content Request**     | **`type`**                     | `"ContentRequest"`                    | **Required**                                                                                                                                           |
|                         | **`actor`**                    | `"alice@alicenet.net"`                | **Required:** "Actor" specifies the address of the sender of the content.                                                                              |
|                         | **`id`**                       | `"92defee110..."`                     | **Required:** The content identifier is required, to be able to assign the pre-data as well as all past and coming interactions to the actual content. |
|                         |                                |                                       |                                                                                                                                                        |
| **Content Response**    | **`type`**                     | `"ContentResponse"`                   | **Required**                                                                                                                                           |
|                         | **`actor`**                    | `"alice@alicenet.net"`                | **Required**                                                                                                                                           |
|                         | **`formatId`**                 | `"post-comments"`                     | **Required:** The format identifier is a string specifying the name of the sent format.                                                                |
|                         | **`content`**                  | `"ZXloOUp2Nn..."`                     | **Required:** The actual content is encrypted symmetrically with the secret.                                                                           |
|                         | `encrypted`                    | `bool(true)`                          | "Encrypted" is true, if the content is encrypted.                                                                                                      |
|                         | `interactions`                 |                                       | Here, the list of interactions to original content can be attached.                                                                                    |
|                         | `interactions.sender`          | `"bob@bobnet.net"`                    | Each interaction needs the information about the address of the interaction's sender.                                                                  |
|                         | `interactions.interaction`     | `"ERt3dsfsdf..."`                     | All interaction contents are symmetrically encrypted with the secret.                                                                                  |
|                         |                                |                                       |                                                                                                                                                        |
| **Format Request**      | **`type`**                     | `"FormatRequest"`                     | **Required**                                                                                                                                           |
|                         | **`formatId`**                 | `"post-comments"`                     | **Required:** The format identifier is a string, specifying the name of the sent format.                                                               |
|                         |                                |                                       |                                                                                                                                                        |
| **Format Response**     | **`type`**                     | `"FormatResponse"`                    | **Required**                                                                                                                                           |
|                         | **`formatId`**                 | `"post-comments"`                     | **Required**                                                                                                                                           |
|                         | **`format`**                   | `"<i>New Message..."`                 | **Required:** "Format" includes information about the structure of the sent content.                                                                   |
|                         |                                |                                       |                                                                                                                                                        |
| **Interaction**         | **`type`**                     | `"Interaction"`                       | **Required**                                                                                                                                           |
|                         | **`actor`**                    | `"bob@bobnet.net"`                    | **Required:** "Actor" specifies the address of the sender of the interaction content.                                                                  |
|                         | **`to`**                       | `"alice@alicenet.net"`                | **Required:** "To" contains the address of the person, who receives the interaction content.                                                           |
|                         | **`id`**                       | `"92defee110..."`                     | **Required:**                                                                                                                                          |
|                         | **`interactionType`**          | `"comment"`                           | **Required:** The interaction type is important for the receiving network to be able to properly process and view the content.                         |
|                         | **`interaction`**              | `"j9345jsdl7..."`                     | **Required:** The interaction content is symmetrically encrypted with the secret.                                                                      |
|                         | `nonce`                        | `"90353ß2350..."`                     | The "number only used once" is used to identify and sort the interactions.                                                                             |
|                         | `encrypted`                    | `bool(true)`                          | "Encrypted" is true, if the interaction content is encrypted.                                                                                          |
|                         |                                |                                       |                                                                                                                                                        |
| **Acknowledgment**      | **`type`**                     | `"ACK"`                               | **Required:** An acknowledgment about the received content is sent to the actor's network.                                                             |
|                         |                                |                                       |                                                                                                                                                        |
| **Error**               | **`type`**                     | `"Error"`                             | **Required**                                                                                                                                           |
|                         | **`error`**                    | `"Error!"`                            | **Required:** The error message includes information about the occurring problem.                                                                      |

**Code added:**
Created PacketBuilder.php, PacketHandler.php, index.php, post_office.php, Processor.php.
Added folder posts for saving content via ID.
Implement and test processing.
