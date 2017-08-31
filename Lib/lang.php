<?php
/**
 * COmanage Registry Email Provisioner Plugin Language File
 *
 * Portions licensed to the University Corporation for Advanced Internet
 * Development, Inc. ("UCAID") under one or more contributor license agreements.
 * See the NOTICE file distributed with this work for additional information
 * regarding copyright ownership.
 *
 * UCAID licenses this file to you under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry vTODO
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

global $cm_lang, $cm_texts;

// When localizing, the number in format specifications (eg: %1$s) indicates the argument
// position as passed to _txt.  This can be used to process the arguments in
// a different order than they were passed.

$cm_email_provisioner_texts['en_US'] = array(
  // Titles, per-controller
  'ct.co_email_provisioner_targets.1'  => 'Email Provisioner Target',
  'ct.co_email_provisioner_targets.pl' => 'Email Provisioner Targets',

  // Plugin texts
  'pl.emailprovisioner.info'      => 'The Email provisioner allows sending email messages to external addresses containing relevant information for administrators to perform a manual provisioning action. This is useable in a wide variety of situations where linking the COManage Registry through automated means is not available. It can be used as a `heads-up` for administrators as well to indicate changes in their back-end systems due to automated provisioning. ',
  'pl.emailprovisioner.template_variables.info' => "Subject and message templates support the use of replacement variables. Currently, the following variables are supported: ''GIVENNAME', 'SURNAME', 'FAMILYNAME', 'COMMONNAME', 'NAME', 'EDUPERSONAFFILIATION', 'EMPLOYEETYPE', 'O', 'OU', 'TITLE', 'EDUPERSONORCID', 'EDUPERSONPRINCIPALNAME', 'EDUPERSONPRINCIPALNAMEPRIOR', 'EDUPERSONUNIQUEID', 'EMPLOYEENUMBER', 'MAIL', 'UID', 'SSHPUBLICKEY', 'FACSIMILETELEPHONENUMBER', 'FAX', 'L', 'MOBILE', 'POSTALCODE', 'ROOMNUMBER', 'ST', 'STREET', 'TELEPHONENUMBER', 'DESCRIPTION', 'MEMBERS', 'HASMEMBER', 'ISMEMBEROF', 'EDUPERSONENTITLEMENT', 'GECOS', 'GIDNUMBER', 'HOMEDIRECTORY', 'UIDNUMBER'. Enclose variable names with curly braces ('{}', e.g.: '{NAME}') to have the field replaced with the relevant value. Names are not case sensitive.",
  'pl.emailprovisioner.adminaddress'      => 'Email address ',
  'pl.emailprovisioner.adminaddress.desc' => 'Address to send email to upon provisioning (comma separated list)',
  'pl.emailprovisioner.template_newuser_subject'      => 'Subject for New User',
  'pl.emailprovisioner.template_newuser_subject.desc' => 'Subject to use for the email provisioning a new CO Person',
  'pl.emailprovisioner.template_newuser_subject.default' => 'Provisioning New User {NAME}',
  'pl.emailprovisioner.template_newuser_template'      => 'Template for New User',
  'pl.emailprovisioner.template_newuser_template.desc' => 'Template to use for the email provisioning a new CO Person',
  'pl.emailprovisioner.template_newuser_template.default' => "Dear Administrator,\r\n\r\nThis e-mail message is meant to inform you of the provisioning of user {NAME}. Please find below the relevant details for this user:\r\n\r\nName: {NAME}\r\nE-mail address: {MAIL}\r\neduPersonPrincipalName: {EDUPERSONPRINCIPALNAME}\r\n\r\nRegards,\r\n\r\nCoManage Registry",
  'pl.emailprovisioner.template_updateuser_subject'      => 'Subject for Update User',
  'pl.emailprovisioner.template_updateuser_subject.desc' => 'Subject to use for email provisioning updates of a CO Person',
  'pl.emailprovisioner.template_updateuser_subject.default' => 'Update Provisioning of User {NAME}',
  'pl.emailprovisioner.template_updateuser_template'      => 'Template for Update User',
  'pl.emailprovisioner.template_updateuser_template.desc' => 'Template to use for the email provisioning updates of a CO Person',
  'pl.emailprovisioner.template_updateuser_template.default' => "Dear Administrator,\r\n\r\nThis e-mail message is meant to inform you of the update provisioning of user {NAME}. Please find below the relevant details for this user:\r\n\r\nName: {NAME}\r\nE-mail address: {MAIL}\r\neduPersonPrincipalName: {EDUPERSONPRINCIPALNAME}\r\n\r\nRegards,\r\n\r\nCoManage Registry",
  'pl.emailprovisioner.template_removeuser_subject'      => 'Subject for Deprovisioning User',
  'pl.emailprovisioner.template_removeuser_subject.desc' => 'Subject to use in the email when a CO Person is deprovisioned',
  'pl.emailprovisioner.template_removeuser_subject.default' => 'Deprovising User {NAME}',
  'pl.emailprovisioner.template_removeuser_template'      => 'Template for Deprovisioning User',
  'pl.emailprovisioner.template_removeuser_template.desc' => 'Template to use for the email when a CO Person is deprovisioned',
  'pl.emailprovisioner.template_removeuser_template.default' => "Dear Administrator,\r\n\r\nThis e-mail message is meant to inform you of the deprovisioning of user {NAME}. Please find below the relevant details for this user:\r\n\r\nName: {NAME}\r\nE-mail address: {MAIL}\r\neduPersonPrincipalName: {EDUPERSONPRINCIPALNAME}\r\n\r\nRegards,\r\n\r\nCoManage Registry",
  'pl.emailprovisioner.template_newgroup_subject'      => 'Subject for New COU',
  'pl.emailprovisioner.template_newgroup_subject.desc' => 'Subject to use for the email provisioning a new COU',
  'pl.emailprovisioner.template_newgroup_subject.default' => 'Provisioning New Group {NAME}',
  'pl.emailprovisioner.template_newgroup_template'      => 'Template for New COU',
  'pl.emailprovisioner.template_newgroup_template.desc' => 'Template to use for the email provisioning a new COU',
  'pl.emailprovisioner.template_newgroup_template.default' => "Dear Administrator,\r\n\r\nThis e-mail message is meant to inform you of the provisioning of a new group named {NAME}. The following users are currently a member of this group: \r\n{HASMEMBER}\r\nRegards,\r\n\r\nCoManage Registry",
  'pl.emailprovisioner.template_updategroup_subject'      => 'Subject for Update COU',
  'pl.emailprovisioner.template_updategroup_subject.desc' => 'Subject to use for email provisioning updates of a COU',
  'pl.emailprovisioner.template_updategroup_subject.default' => 'Update Provisioning of Group {NAME}',
  'pl.emailprovisioner.template_updategroup_template'      => 'Template for Update COU',
  'pl.emailprovisioner.template_updategroup_template.desc' => 'Template to use for the email provisioning updates of a COU',
  'pl.emailprovisioner.template_updategroup_template.default' => "Dear Administrator,\r\n\r\nThis e-mail message is meant to inform you of the update provisioning of the group named {NAME}. The following users are currently a member of this group: \r\n{HASMEMBER}\r\nRegards,\r\n\r\nCoManage Registry",
  'pl.emailprovisioner.template_removegroup_subject'      => 'Subject for Deprovisioning COU',
  'pl.emailprovisioner.template_removegroup_subject.desc' => 'Subject to use in the email when a COU is deprovisioned',
  'pl.emailprovisioner.template_removegroup_subject.default' => 'Deprovisioning Group {NAME}',
  'pl.emailprovisioner.template_removegroup_template'      => 'Template for Deprovisioning COU',
  'pl.emailprovisioner.template_removegroup_template.desc' => 'Template to use for the email when a COU is deprovisioned',
  'pl.emailprovisioner.template_removegroup_template.default' => "Dear Administrator,\r\n\r\nThis e-mail message is meant to inform you of the deprovisioning of the group named {NAME}. The following users are currently a member of this group: \r\n{HASMEMBER}\r\nRegards,\r\n\r\nCoManage Registry",
);
