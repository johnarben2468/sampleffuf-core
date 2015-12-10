========
Metadata
========

Class Metadata can be stored in different formats:

- XML
- PHP
- JSON

There is Metadata that is defined per class and per property:

* Per Class
    * `default-deserialize-event`
    * `default-serialize-event`
    * `ignore-all`

* Per property
    * `readonly`
    * `aliases`
    * `types`
    * `events`

Ignoring Attributes
-------------------

Sometimes only a set of properties should be used for serialization/deserialization. You can specify which properties
shouldn't be serialized/deserialization.

Usage:

PHP:

.. code-block:: php

    return array(
        'ignore' => array(
            'ignore1' => true,
            'ignore2' => true
        )
    );

XML:

::

    <?xml version="1.0" encoding="utf-8"?>
    <metadata xmlns="http://a15l.com/schemas/serialization/metadata"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="http://a15l.com/schemas/serialization/metadata http://localhost/schema/serialization/metadata/class9.xsd">
        <class>
            <ignore>
                <property name="ignore1"/>
                <property name="ignore2"/>
            </ignore>
        </class>
    </metadata>

JSON:

::

    {
        "ignore": {
            "ignore1": true,
            "ignore2": true
        }
    }


.. tip::

    If you want to ignore all properties of a class use ignore-all

Usage:

PHP:

.. code-block:: php

    return array(
        'ignore-all' => true
    );


XML:

::

    <?xml version="1.0" encoding="utf-8"?>
    <metadata xmlns="http://a15l.com/schemas/serialization/metadata"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="http://a15l.com/schemas/serialization/metadata http://localhost/schema/serialization/metadata/class9.xsd">
        <class ignore-all="true"/>
    </metadata>

JSON:

::

    {
        "ignore-all": true
    }

Default Events
--------------

`default-deserialize-event`
`default-serialize-event`

Readonly properties
-------------------

Sometimes you don't want that the data of properties is set. You can mark properties as readonly to achieve this.

Usage:

PHP:

.. code-block:: php

    return array(
        'readonly' => array(
            'readonly1' => true,
            'readonly2' => true
        )
    );

XML:

::

    <?xml version="1.0" encoding="utf-8"?>
    <metadata xmlns="http://a15l.com/schemas/serialization/metadata"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="http://a15l.com/schemas/serialization/metadata http://localhost/schema/serialization/metadata/class9.xsd">
        <class>
            <readonly>
                <property name="readonly1"/>
                <property name="readonly2"/>
            </readonly>
        </class>
    </metadata>

JSON:

::

    {
        "readonly": {
            "readonly1": true,
            "readonly2": true
        }
    }


Aliasing
--------

Sometimes serialized attributes must be named differently.

For example you have the following class:

.. code-block:: php

    class User{

        private $uid;
        private $fName;

    }

And you want to rename the attributes in the serialized object to:

========  =========
Old name  New name
========  =========
uid       userId
fName     firstName
========  =========

So you define the aliases as following:

PHP:

.. code-block:: php

    return array(
        'aliases' => array(
            'uid' => 'userId',
            'fName' => 'firstName'
        )
    );

XML:

::

    <?xml version="1.0" encoding="utf-8"?>
    <metadata xmlns="http://a15l.com/schemas/serialization/metadata"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="http://a15l.com/schemas/serialization/metadata http://localhost/schema/serialization/metadata/class9.xsd">
        <class>
            <aliases>
                <alias property="uid" name="userId"/>
                <alias property="fName" name="firstName"/>
            </aliases>
        </class>
    </metadata>

JSON:

::

    {
        "aliases": {
            "uid": "userId",
            "fName": "firstName"
        }
    }



Data types
----------

A data type must be provided for properties of non scalar data types (objects,array).
For all other properties a data type can be provided.

Available data types:

+--------------+--------------------------------------------------+----------------------------------------------------+
| Type         | Description                                      |                                                    |
+==============+==================================================+====================================================+
| DateTime     | During the serialization the DateTime instance   | Format see PHP Date formats                        |
|              | will be converted to the specified format in the | (e.g. `YYY-mm-dd H:i:s`)                           |
|              | value-field                                      |                                                    |
|              | During the deserialization a DateTime instance   |                                                    |
|              | will be created from the input using the         |                                                    |
|              | specified format in the value-field              |                                                    |
+--------------+--------------------------------------------------+----------------------------------------------------+
| object       | During the serialization the instance will be    | Fully qualified class name                         |
|              | serialized according their defined class         | (for example: vendor\\ns\\Bar)                     |
|              | configuration (ignored, readonly                 |                                                    |
|              | properties e.g.). Note: the specified class in   |                                                    |
|              | value-filed **will be ignored!**                 |                                                    |
|              | During the deserialization a instance will be    |                                                    |
|              | created and the property values will be populated|                                                    |
|              | under the consideration of the class config file.|                                                    |
|              | Note: the specified class in value-filed         |                                                    |
|              | **will be used** for the object instantiation    |                                                    |
+--------------+--------------------------------------------------+----------------------------------------------------+
| object-array | Same as object, used for multiple instances.     | Fully qualified class name                         |
|              | For example a property, that stores instances of | Note: Standard predefined classes are not allowed  |
|              | another class in an array                        | (including DateTime!)                              |
+--------------+--------------------------------------------------+----------------------------------------------------+
| scalar-array | Used during the deserialization to convert the   | Scalar data type (integer,boolean,string,float)    |
|              | submitted values of an array to the specified    |                                                    |
|              | scalar data type                                 |                                                    |
+--------------+--------------------------------------------------+----------------------------------------------------+
| array        | No casting will be performed for this data type. | `----`                                             |
|              | If the provided value is not an array, the value |                                                    |
|              | will be casted to an empty array!                |                                                    |
+--------------+--------------------------------------------------+----------------------------------------------------+
| boolean      |                                                  | `----`                                             |
+--------------+                                                  |                                                    |
| integer      | Note: if these types are specified and an array  |                                                    |
+--------------+ is provided during the deserialization, then     |                                                    |
| float        | value will be casted to null!                    |                                                    |
+--------------+                                                  |                                                    |
| string       |                                                  |                                                    |
+--------------+--------------------------------------------------+----------------------------------------------------+

`PHP Date formats <http://php.net/manual/en/function.date.php#refsect1-function.date-parameters>`_.

.. note::

    If no value for the data type DateTime is provided, the value 'r' for "RFC 2822 formatted date" will be used.

Usage:

For example you have the following classes :

.. code-block:: php

    namespace vendor\foo;

    class User{

        private $uid;
        private $fName;

        /**
         * @var Address
         */
        private $mainAddress;

       /**
         * @var Address[]
         */
        private $otherAddresses = array();

        /**
         * @var DateTime
         */
        private $creationDate;

    }

    class Address{

        private $zip;
        private $street;

    }

For the serialization/deserialization of the user instance you have to provide following types:

PHP:

.. code-block:: php

    return array(
        'types' => array(
            'mainAddress' => array(
                'object' => 'vendor\foo\Address'
            ),
            'otherAddresses' => array(
                'object-array' => 'vendor\foo\Address'
            ),
            'creationDate' => array(
                'DateTime' => 'Y-m-d H:i:s'
            )
        )
    );

XML:

::

    <?xml version="1.0" encoding="utf-8"?>
    <metadata xmlns="http://a15l.com/schemas/serialization/metadata"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="http://a15l.com/schemas/serialization/metadata http://localhost/schema/serialization/metadata/class9.xsd">
        <class>
            <types>
                <property name="mainAddress" type="object" value="vendor\foo\Address"/>
                <property name="otherAddresses" type="object-array" value="vendor\foo\Address"/>
                <property name="creationDate" type="DateTime" value="Y-m-d H:i:s"/>
            </types>
        </class>
    </metadata>

JSON:

::

    {
      "types": {
        "mainAddress": {
          "object": "vendor\\foo\\Address"
        },
        "otherAddresses": {
          "object-array": "vendor\\foo\\Address"
        },
        "creationDate": {
          "DateTime": "Y-m-d H:i:s"
        }
      }
    }



Events
------

`events`
