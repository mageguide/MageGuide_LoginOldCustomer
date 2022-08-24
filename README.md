# MageGuide LoginOldCustomer
[Deprecated]

Tested on: 2.2+, 2.3+

## Description

  A common issue when eshops are being replatformed is that Old Customers are not able to login new Magento Setup by using their old credentials, since passwords are being encrypted with different encryption algorithm. LoginOldCustomer provides a nice work around in order to force your old customers to reset their passwords. LoginOldCustomer recognize old customers that did at least try to login to the new site using their old credentials.


### Functionalities

  - Old Customer Recognition
  - Reset Password Triggering
  - Global Message Addition
  - Logs available on `/var/log/login-old-customers-log.log`

### Installation

  Add the app folder with all the subfolders into the root folder of your Magento Application.

  Perform the following commands:

  * __Developer Mode__

```sh

    $ php bin/magento set:upg && php bin/magento c:c

```

  * __Production Mode__

```sh

    $ php bin/magento maintenance:enable
    $ php bin/magento setup:upgrade
    $ php bin/magento setup:di:compile
    $ php bin/magento setup:upgrade
    $ php bin/magento setup:static-content:deploy el_GR en_US #or any other space seperated language you need for your project
    $ php bin/magento maintenance:disable

```

### Usage

Import your old customers on Magento and add to them 2 text customer attributes, **flag_is_old** with value "1" and **old_customer_code** with the customer id of the old website. 

Replace "207" at line 70 of `app/code/MageGuide/LoginOldCustomer/Plugin/LoginPostBefore.php` with the attribute id of **flag_is_old** attribute. 

It would be usefull to replace "examplemageguide.com" at line 89 with your eshop's domain!

You are ready to go! You can keep track of the progress at `/var/log/login-old-customers-log.log`