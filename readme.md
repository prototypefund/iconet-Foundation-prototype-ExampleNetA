## Iconet integration into ExampleNetA
Check Setup.md for Setup Instructions.

This repository is part of the iconet prototype development, where two networks are linked through the iconet mechanisms.
This is very basic network, with a low feature set and the code uses no frameworks. This allows for a quite untangled demonstration, on how to integrate the iconet technology. 
ExampleNetA is taken from: https://github.com/yaswanthpalaghat/Social-Network-using-php-and-mysql
### A: Development Doku
Here we document the development from ExampleNetA towards interconnectivtiy.
#### Contactlist Feature
Feature reasoning:
In its initial featureset, Users of ExampleNetA have no ability to display or manage contacts/friends. So far the friends feature of ExampleNetA only enables adding and removing off friends, where beeing friends entails that posts are displayed within the friends feed.
While iconet does not set any requirements on how contacts/friends are handled by a network, there needs to be some way for a user to add and manage external addresses and hold them in a contactlist, so they then can send messages towards them. 
Feature added:
Since ExampleNetA has no such feature, we add it. We change the requests.php to contacts.php, which previously only displayed pending friend requests.
A user will be able to display all their friends, be able to remove them from this view and be able to add external addresses.


#### Iconet-Address generation Feature - open todo
Feature reasoning:
Each user, to be able to be addressed by users of external networks, needs some global address.
This will be the local identifier of a user and the global address of the network, written like localID@globalURL
Feature added:
We will asume, ExampleNetA holds the global URL ExampleNetA.net
Also, on User registration, a global Address is automaticly generated.
This address will be displayed on the home site of the current loggid in user, aswell as in each users profile.
Note: on this address generation all iconet infrastructure like global-inbox etc. will also be initiated for a user, but these will be added on adding those features.




#### Known bugs of exampleNetA
ExampleNetA did some unclean implementations, which do not interfere with our demo implementations.
We'll just accept those, and gather them here.
- Friends are saved by username, but username is not unique. Users with same names are not prohibited, but will create bugs.

