========
Overview
========
YAPCDI (Yet Another PHP Container For Dependency Injections) is a 
dependency injector that recursively detects and instantiates 
class dependencies. 

It uses the following thechnologies for that:
#. Autowiring 
#. Configurations 

.. note::
    YapCDI is not a Service Locator!



Requirements
============
#. PHP 5.4.0

Installation
============

.. code-block:: js

    {
      "require": {
         "yapcdi/yapcdi": "~1.0"
      }
   }

Usage
=====
Container
Class dependency resolver