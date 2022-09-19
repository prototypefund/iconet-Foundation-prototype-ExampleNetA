## Iconet integration into ExampleNetA
Check Setup.md for Setup Instructions.

This repository is part of the iconet prototype development, where two networks are linked through the iconet mechanisms.
This is very basic network, with a low feature set and the code uses no frameworks. This allows for a quite untangled demonstration, on how to integrate the iconet technology. 
ExampleNetA is taken from: https://github.com/yaswanthpalaghat/Social-Network-using-php-and-mysql
### A: Development Doku
Here we document the development from ExampleNetA towards interconnectivity.

#### Feature: Separate Infrastructure
Reason:

Add a separate database and infrastructure for all iconet related aspects.
While networks might want the iconet features to be rather integrated, for showcasing this is ideal.

Feature added:

iconet database table generation, folders for iconet handlers.  

#### Feature: Contactlist
Reason:

In its initial feature-set, Users of ExampleNetA have no ability to display or manage contacts/friends. So far the friends feature of ExampleNetA only enables adding and removing off friends, where being friends entails that posts are displayed within the friends feed.
While iconet does not set any requirements on how contacts/friends are handled by a network, there needs to be some way for a user to add and manage external addresses and hold them in a contact-list, so they then may send messages towards them. 

Feature added:

Since ExampleNetA has no such feature, we add it. We change the requests.php to contacts.php, which previously only displayed pending friend requests.
A user will be able to display all their internal friends and friends-requests, as well as their external contacts.


#### Feature: Iconet-Address generation - open todo
Reason:

Each user, to be able to be addressed by users of external networks, needs some global address.
This will be the local identifier of a user and the global address of the network, written like localID@globalURL

Feature added:

Assume, ExampleNetA holds the global URL ExampleNetA.net
On User registration, a global Address is automatically generated.
This address will be displayed on the home site of the current user, as well as in each user's profile.

#### Feature: Basic S2S: Return PubKey of Users - Open ToDo
Reason:

Required to provided external sources with pubkey of local users.

Feature added:

Generate internal Server2Server handling structure. For now use trivial placeholders for external s2s communication.
Return Pubkey on request.
    
    Assume Packetstructure: 
    Incoming:
    {
    GET Pubkey:
    useraddress = %address
    }
    Response:
    {
    SEND Pubkey:
    useraddress = %address
    pubkey = %pubkey
    }

