# comanage-emailprovisioner
This is a plugin for the [COManage Registry](https://www.internet2.edu/products-services/trust-identity/comanage/) application as provided and maintained by the [Internet2](https://www.internet2.edu/) foundation.

This project has the following deployment goals:
- create a Provisioner Plugin for COManage that sends out emails upon provisioning


COManage EmailProvisioner Plugin
================================
In existing administrative processes for collaborations, people usually send out an e-mail to a central service administrator requesting access to specific features. While this is not scalable to larger collaborations, it serves a purpose for the more abundant smaller groups.

This plugin allows sending out an e-mail to an administrator address upon provisioning of people or groups. It features the possibility of adjusting 6 different e-mail templates for different provisioning events, allowing the provisioning administrator to incorporate template variables to personalize the e-mail with relevant information.

Please note that this is not a replacement for any automated provisioning. This plugin will send a lot of e-mails and does not check whether the information send out has changed in any meaningfull way with respect to an earlier provisioning action. It is to be used as a provisioning link-up of last resort and can be used as a step-in replacement for existing situations, where groups are already used to sending e-mails to service administrators.
A follow-up action would be to link the service administrator to a different, automated provisioning platform, like LDAP.

Setup
=====
The provisioning plugin must be installed in the `local/Plugin` directory of the COManage installation. Optionally, you can install it in the `app/AvailablePlugins` directory and link to it from the `local/Plugin` directory.

After installation, run the Cake database update script as per the COManage instructions:
```
app/Console/cake database
```
You can now select the EmailProvisioner plugin for your COManage Registry groups.

Configuration
=============
The EmailProvisioner allows configuration of a single recipient address and 6 different templates for the following provisioning actions:
- NewPerson: provisioning a new COPerson
- UpdatePerson: provisioning a COPerson when any related information has changed
- RemovePerson: deprovisioning a COPerson
- NewGroup: provisioning a new COU
- UpdateGroup: provisioning a COU when any related information has changed
- RemoveGroup: deprovisioning a COU

Please note that the UpdatePerson and UpdateGroup provisioning actions are activated for even the smallest status change in the relevant COPerson and COU. This will lead to a lot of e-mails to the service administrator that seemingly contain no change of information.

Tests
=====
This plugin comes with unit tests for the mail CoEmailProvisionerTarget model. Access the Cake unit test page at:
````
<your path>/registry/test.php
````
You can select the CoEmailProvisioner plugin for testing there. Code coverage should be 100%.

Disclaimer
==========
This plugin is provided AS-IS without any claims whatsoever to its functionality. The code is based partially on COManage Registry code, distributed under the [Apache License 2.0](http://www.apache.org/licenses/LICENSE-2.0).

