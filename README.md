CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration


INTRODUCTION
------------

The Review Questions module provides Questions and Answers functionality
which can be enabled on available content types. The Questions get shown
on the node page as a label along with the Answer textarea. The authenticated
users has only access to view Questions and submit the answers. The module
defines the Answer entity type which holds Answers to the questions. The
module provides Answers listing with the help of views module. When answers
are submitted, the details are emailed to the configured email addresses.

REQUIREMENTS
------------

This module requires following core and contrib module:
* Options
* Text
* Field
* Node
* Views
* Paragraphs (https://www.drupal.org/project/paragraphs)


INSTALLATION
------------

Install the Review Questions module as you would normally install a contributed
Drupal module. Visit https://www.drupal.org/node/1897420 for further
information.


CONFIGURATION
-------------

    1. Navigate to Administration > Extend and enable the Review Questions module.
    2. Navigate to Administration > Configuration > Review Questions Settings.
       Select content types and add email addresses one per line and Hit
       Save configuration.
    3. Create a node of enabled content type from step 2 along with
       Review Questions paragraphs. If Show Question? field is checked
       then only Questions are visible on the node page.
    4. Add Answers and Hit Save Answer button, Check email addresses added
       in step 2 for Answer details.
    5. To see Answer entities submissions, Verify the user role has
       permission "Administer Answers Entities" and Go to
       "/review-questions/submissions".

