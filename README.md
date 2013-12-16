0.6-basic
=====

About Basic
=====
IMGBB 0.6, known as BASIC, is a standalone version of Image Bulletin Board with only the basic components
which allow the board to function - the three interactive applications with their corresponding modules,
the inherent back-end logic, and the admin modules. The following features and/or aspects are deliberately
NOT present consistently throughout BASIC, although you may see hints of them here and there or functions that
look very bare-bones and clearly are supposed to later contain it:

**SECURITY** -- Cryptography, among other things, is almost completely omitted during this phase.

**CONTROLLER** -- The object known as the Controller is almost completely barebones, all coding time is spent
giving solidity to the various modules and application in general in order to allow the front-end developers
to do their work. As having as many people working at the same time is the key here, the time it would take to
solve the various difficulties of rewrites and logic-switching does not outweigh the time of front-end devs
that would be wasted.

**JavaScript** -- JavaScript and all Ajax handlers are completely omitted during this phase. Getting the three
applications completely functional is the first priority - as the software is currently relatively small
(only a few thousand lines), I have decided that it is easier to expand from a basic version of the software
-- hence, BASIC -- than expect to rewrite perfection straight out of the IDE. Far faster, from my experience at least!

**Libraries** -- No libraries other than PHPTAL will be in use during BASIC.

**Caching System** -- The caching system will NOT be created in this phase.

**Template engine** -- The template engine will NOT be created in this phase. In its place, I am using PHPTAL. It would
be far more economical to first understand *what* we want and then build the template engine in that vision. While
the *correct* way would be to first have everybody meet up, get a good reading on exactly how we would design the
templates, what we need, etc, and then build the application *in that vision* instead of the other way around. However,
I do not have the luxury of having a front-end team that I can simply meet up nor one that is interested in
comprehensive meetings. Therefor, I have decided to delay the tempalte engine until we have all of our templates ready,
and then rewrite the templates to fit the new template engine.

**Themes** -- The only theme that would be created in this phase, and for the foreseeable future, is the current
IMGBB theme written inherently within the CSS files. Interested parties that commit to our website early on will
receive various private on-the-house conversions of their themes.

**Installation Scripts** -- Installation instructions and scripts will NOT be created in this phase.




Project leader - D.B Truthlight

Designer - Grandil

JavaScript - "Josh"