==============================================================================
Breaking: #68354 - Uniform extension directory structure of EXT:indexed_search
==============================================================================

Description
===========

The directory structure of the extension "Indexed Search" has been streamlined.


Impact
======

All language files are now locaced in directory Resources/Private/Language, the template files in Resources/Private/Templates.
Icons from pi/res directory have been moved to Resources/Public/Icons, images to Resources/Public/Images.


Affected Installations
======================

Installations that use EXT:indexed_search.


Migration
=========

Make sure your configuration matches with new directory structure.