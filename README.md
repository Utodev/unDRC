# unDRC
A decompiler for DAAD games

Fair use of unDRC
=================

unDRC allows you to decompile DAAD games. If you are going to use it for someone else's game, ask for permission and ensure reverse engineering is legal in your country.

If it's your game, keep in mind you will not get exactly the same code, but one that will most likely generate the same game. One thing you will miss is any comment you may have added, as comments are not included in the final game, but also you will not find any "define" you may have created. That is completely normal and there is no way to revert to original code.


Why unDRC if unDAAD already exists?
===================================

Basically, unDAAD was made to decompile MSDOS DDB files from old Aventuras AD games, which was a bit complicated because the different games were made with different versions of DAAD. That implied a lot of code that was just looking for specific games. Although at some point I decided to include other targets, the original code wasn't thought for that and it was all a bit messy.

Lately, most games are made with DRC (within DAAD Ready), or with latest version of the original compiler, so it made no sense to depend on that old code.

Also, refactoring the code has helped to improve things, being able now to identify Maluva condacts, use identifiers for flags and objects, and being able to identify DRC code optimizations.

unDRC is also able to extract games generated with old compiler, while it's DC 2.0 or above.


How to use unDRC
================

unDRC is a php script, so in order to use you have to install PHP. 

- Windows: Download it and extract it in any folder (https://windows.php.net/download/). PHP version 7 or above is required.
- Linux: Install php by using your usual package manager.
- MacOS: from MacOS X (10.0.0) up to MacOS Big Sur (11.0.0) php was bundled with the OS, so you have to do nothing.
  From MacOS Monterey (12.0.0) it is not so you'll have to install it. You first will have to install Homebrew (https://wpbeaches.com/installing-homebrew-on-macos-big-sur-11-2-package-manager-for-linux-apps/)  and then from a terminal, type "brew install shivammathur/php/php@7.4"


Once php is installed, you can run unDRC from any terminal by going to the folder where unDRC is, and running:

> php unDRC.php

That will provide instructions about how to use.

In Windows, you would propably have to add the path to php.exe. So if you extacted the content in C:\PHP rather than the command line above, you would have to enter

> C:\php\php unDRC.php

A common call to unDRC could be:

> php unDRC.php MYGAME.DDB

Or, if you don't have the DDB file, but another file (i.e. a TAP file, BIN file, etc.)

> php unDRC.php MYGAME.TAP -a

Windos equivalents if you installed PHP at c:\php may be:

> c:\php\php unDRC.php MYGAME.DDB
> c:\php\php unDRC.php MYGAME.TAP -a

There are other optional parameters aside of this "-a" you have seem. See below:

Notes about parameters
======================

-a : will try to identify a DDB header within a file. It can be used with Spectrum .SNA files, .BIN files from Amstrad CPC, C64 DDB files which have the common C64 two-byte header, etc. Please notice DDB identification is not easy, so a false positive may happen, which would eventually lead to failures in the extraction, garbage on screen, etc. This is absolutely normal.Idenfification is made by finding a "2" byte, followed by another byte whose lower nibble is 0 or 1, followed by a 95. This procedure cannot find MSX2 DDB files because that 95 byte is used for something else.

-m  : By default, unDRC will try to identify EXTERN calls as Maluva calls. That may lead to wrong code extraction if the DDB includes EXTERN code, but it's not Maluva extension. This    option will not generate Maluva pseudo-condact, but just leave the EXTERN call as is. Please notice Maluva pseudocondacts XMES, XMESSAGE and XBEEP use a third parameter, so disabling
Maluva indentification on games that use those condacts may lead to wrong extraction of the code from that moment on.

-i  : By default, unDRC does not generates messages in the MTX section, unless one of them is never directly used in the processes. Instead, MTX section will be empty, and message strings will be inline in the processes, DRC style. You can disable this by using this option, then messages will go to MTX section, and MESSAGE and MES calls will just have a message number as parameter. Text in the message will be added as comment after the MES/MESSAGE call, to help with readability.

-o : By default, unDRC creates object identifiers automaticall based in the object description (i.e. "some sugar" would generate oSomeSugar). Those idenfiers will be later used in the processeses, so instead of "CARRIED 4" you can see "CARRIED oSomeSugar". You can disable this feature by using -o parameter.


-t  : By default, uDRC does not generate a .tok file. Tokens are used to decompile DDB file, but not exported. With -t, a .tok file would be created in the format used by DRC. You can put the tok file beside the .dsf one when compiling in order to get those specific tokens used. Please notice, although most of games use same tokens for text compression, there is a chance the author used different ones, so in case after decompiling and recompiling again, you get a bigger DDB file, you may try to extract the tokens and used them.

Contact
=======
Please report any issue in the DAAD Ready Telegram channel (https://t.me/daadready) or to @uto_dev at Twitter. You can also open an issue at GitHub (https://github.com/Utodev/unDRC)

If you are using a file that is not the DDB file itself, and when using -a parameter you get garbage on screen, please notice sometimes findind the DDB file within another file is just impossible, and that garbage may not be a bug, just the DDB was not properly idenfied. Make sure you are using the right file, and try to use a proper DDB file instead before asking for (most likely impossible) fixes.
