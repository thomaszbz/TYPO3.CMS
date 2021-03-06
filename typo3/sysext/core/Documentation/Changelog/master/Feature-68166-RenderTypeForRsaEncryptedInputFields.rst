===========================================================
Feature: #67932 - RenderType for rsa encrypted input fields
===========================================================

Description
===========

The rsaauth extension defines an own render type for TCA input fields. Those fields will be encrypted before submitting the form.


Impact
======

In the Backend password fields for backend and frontend users are automatically encoded before the form is submitted.

Usage
=====

To encrypt your own TCA fields you can add define the render type ``rsaInput``.

.. code-block:: php

$GLOBALS['TCA']['be_users']['columns']['password']['config']['renderType'] = 'rsaInput';
