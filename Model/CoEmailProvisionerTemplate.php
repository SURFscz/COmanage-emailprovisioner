<?php
/**
 * COmanage Registry CO Email Provisioner Template Model
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

class CoEmailProvisionerTemplate extends AppModel {
  // Define class name for cake
  public $name = "CoEmailProvisionerTemplate";

  // Add behaviors
  public $actsAs = array('Containable');

  // Association rules from this model to other models
  public $belongsTo = array(
    "EmailProvisioner.CoEmailProvisionerTarget"
  );

  // Default display field for cake generated views
  public $displayField = "template";

  // Validation rules for table elements
  public $validate = array(
    'co_email_provisioner_target_id' => array(
      'rule' => 'numeric',
      'required' => true,
      'message' => 'A CO Email Provisioning Target ID must be provided'
    )
  );

  /**
   * Determine the required template
   *
   * @since  COmanage Registry vTODO
   * @param  Array COEmailProvisionerTarget
   * @param  EmailTemplateEnum
   * @return Array EmailProvisionerTemplate
   */

  public function loadTemplate($target, $template) {
  }

  /**
   * Map a set of CO Group Members to their DNs.
   *
   * @since  COmanage Registry v0.8.2
   * @param  Array CO Group Members
   * @return Array Array of DNs found -- note this array is not in any particular order, and may have fewer entries
   */

  public function dnsForMembers($coGroupMembers) {
    return $this->mapCoGroupMembersToDns($coGroupMembers);
  }

  /**
   * Map a set of CO Group Member owners to their DNs.
   *
   * @since  COmanage Registry v0.8.2
   * @param  Array CO Group Members
   * @return Array Array of DNs found -- note this array is not in any particular order, and may have fewer entries
   */

  public function dnsForOwners($coGroupMembers) {
    return $this->mapCoGroupMembersToDns($coGroupMembers, true);
  }

  /**
   * Map a set of CO Group Members to their DNs. A similar function is in CoGroupMember.php.
   *
   * @since  COmanage Registry v0.8.2
   * @param  Array CO Group Members
   * @param  Boolean True to map owners, false to map members
   * @return Array Array of DNs found -- note this array is not in any particular order, and may have fewer entries
   */

  private function mapCoGroupMembersToDns($coGroupMembers, $owners=false) {
    // Walk through the members and pull the CO Person IDs

    $coPeopleIds = array();

    foreach($coGroupMembers as $m) {
      if(($owners && $m['CoGroupMember']['owner'])
         || (!$owners && $m['CoGroupMember']['member'])) {
        $coPeopleIds[] = $m['CoGroupMember']['co_person_id'];
      }
    }

    if(!empty($coPeopleIds)) {
      // Now perform a find to get the list. Note using the IN notation like this
      // may not scale to very large sets of members.

      $args = array();
      $args['conditions']['CoLdapProvisionerDn.co_person_id'] = $coPeopleIds;
      $args['fields'] = array('CoLdapProvisionerDn.co_person_id', 'CoLdapProvisionerDn.dn');

      return array_values($this->find('list', $args));
    } else {
      return array();
    }
  }

  /**
   * Obtain a DN for a provisioning subject, possibly assigning or reassigning one.
   *
   * @since  COmanage Registry v0.8.2
   * @param  Array CO Provisioning Target data
   * @param  Array CO Provisioning data
   * @param  String Mode: 'group' or 'person'
   * @param  Boolean Whether to assign a DN if one is not found and reassign if the DN should be changed
   * @return Array An array of the following:
   *               - olddn: Old (current) DN (may be null)
   *               - olddnid: Database row ID of old dn (may be null, to facilitate delete)
   *               - newdn: New DN (may be null)
   *               - newdnerr: Error message if new in cannot be assigned
   * @throws RuntimeException
   */

  public function obtainDn($coProvisioningTargetData, $provisioningData, $mode, $assign=true) {
    $curDn = null;
    $curDnId = null;
    $newDn = null;
    $newDnErr = null;

    // First see if we have already assigned a DN

    $args = array();
    $args['conditions']['CoLdapProvisionerDn.co_ldap_provisioner_target_id'] = $coProvisioningTargetData['CoLdapProvisionerTarget']['id'];
    if($mode == 'person') {
      $args['conditions']['CoLdapProvisionerDn.co_person_id'] = $provisioningData['CoPerson']['id'];
    } else {
      $args['conditions']['CoLdapProvisionerDn.co_group_id'] = $provisioningData['CoGroup']['id'];
    }
    $args['contain'] = false;

    $dnRecord = $this->find('first', $args);

    if(!empty($dnRecord)) {
      $curDn = $dnRecord['CoLdapProvisionerDn']['dn'];
      $curDnId = $dnRecord['CoLdapProvisionerDn']['id'];
    }

    // We always try to (re)calculate the DN, but only store it if $assign is true.

    try {
      if($mode == 'person') {
        $newDn = $this->assignPersonDn($coProvisioningTargetData, $provisioningData);
      } else {
        $newDn = $this->assignGroupDn($coProvisioningTargetData, $provisioningData);
      }
    }
    catch(Exception $e) {
      // Rather than throw an exception, store the error in the return array.
      // We do this because there are many common times we will fail to assign a
      // DN (especially on user creation and deletion), so we'll pass the error
      // up the stack and let the calling function decide what to do.

      $newDnErr = $e->getMessage();
    }

    if($assign) {
      // If the the DN doesn't match the existing DN (including if there is no
      // existing DN), update it

      if($newDn && ($curDn != $newDn)) {
        $newDnRecord = array();
        $newDnRecord['CoLdapProvisionerDn']['co_ldap_provisioner_target_id'] = $coProvisioningTargetData['CoLdapProvisionerTarget']['id'];
        if($mode == 'person') {
          $newDnRecord['CoLdapProvisionerDn']['co_person_id'] = $provisioningData['CoPerson']['id'];
        } else {
          $newDnRecord['CoLdapProvisionerDn']['co_group_id'] = $provisioningData['CoGroup']['id'];
        }
        $newDnRecord['CoLdapProvisionerDn']['dn'] = $newDn;

        if(!empty($dnRecord)) {
          $newDnRecord['CoLdapProvisionerDn']['id'] = $dnRecord['CoLdapProvisionerDn']['id'];
        }

        if(!$this->save($newDnRecord)) {
          throw new RuntimeException(_txt('er.db.save'));
        }
      }
    }

    return array('olddn'    => $curDn,
                 'olddnid'  => $curDnId,
                 'newdn'    => $newDn,
                 'newdnerr' => $newDnErr);
  }
}
