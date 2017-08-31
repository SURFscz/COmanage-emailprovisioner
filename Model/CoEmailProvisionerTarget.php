<?php
/**
 * COmanage Registry CO Email Provisioner Target Model
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

App::uses("CoProvisionerPluginTarget", "Model");
App::uses('CakeEmail', 'Network/Email');

class CoEmailProvisionerTarget extends CoProvisionerPluginTarget {
  // Define class name for cake
  public $name = "CoEmailProvisionerTarget";

  // Add behaviors
  public $actsAs = array('Containable');

  // Association rules from this model to other models
  public $belongsTo = array("CoProvisioningTarget");

  public $hasMany = array(
    "CoEmailProvisionerTemplate" => array(
      'className' => 'EmailProvisioner.CoEmailProvisionerTemplate',
      'dependent' => true
    )
  );

  // Default display field for cake generated views
  public $displayField = "no such field";

  // Validation rules for table elements
  public $validate = array(
    'co_provisioning_target_id' => array(
      'rule' => 'numeric',
      'required' => true,
      'message' => 'A CO Provisioning Target ID must be provided'
    ),
    'adminaddress' => array(
      'rule' => array('custom', '/^.*@.*\..*/'),
      'required' => true,
      'allowEmpty' => false,
      'message' => 'Please enter a valid recipient email address'
    )
  );

  public $_emailer=null; // CakeEmail implementation, abstracted for testing purposes

  /**
   * Provision for the specified CO Person.
   *
   * @since  COmanage Registry vTODO
   * @param  Array CO Provisioning Target data
   * @param  ProvisioningActionEnum Registry transaction type triggering provisioning
   * @param  Array Provisioning data, populated with ['CoPerson'] or ['CoGroup']
   * @return Boolean True on success
   * @throws InvalidArgumentException If $coPersonId not found
   * @throws RuntimeException For other errors
   */

  public function provision($coProvisioningTargetData, $op, $provisioningData) {
    $template=null;
    switch($op) {
      case ProvisioningActionEnum::CoPersonAdded:
        $template = EmailTemplateEnum::NewPerson;
        break;
      case ProvisioningActionEnum::CoPersonDeleted:
        $template = EmailTemplateEnum::RemovePerson;
        break;
      case ProvisioningActionEnum::CoPersonPetitionProvisioned:
      case ProvisioningActionEnum::CoPersonPipelineProvisioned:
      case ProvisioningActionEnum::CoPersonReprovisionRequested:
      case ProvisioningActionEnum::CoPersonUnexpired:
        // Potentially, the CoPerson group information changed, so
        // we need to provision all the groups of this CoPerson
        // For ease of use, we treat this as a modify
        $template = EmailTemplateEnum::UpdatePerson;
        break;
      case ProvisioningActionEnum::CoPersonExpired:
      case ProvisioningActionEnum::CoPersonEnteredGracePeriod:
      case ProvisioningActionEnum::CoPersonUnexpired:
      case ProvisioningActionEnum::CoPersonUpdated:
        if(!in_array($provisioningData['CoPerson']['status'],
                     array(StatusEnum::Active,
                           StatusEnum::Expired,
                           StatusEnum::GracePeriod,
                           StatusEnum::Suspended))) {
          // Convert this to a delete operation. Basically we (may) have a record,
          // but the person is no longer active.
          $template = EmailTemplateEnum::RemovePerson;
        } else {
          // An update may cause an existing person to be provisioned for the first time
          // or for an unexpectedly removed entry to be replaced
          $template = EmailTemplateEnum::UpdatePerson;
        }
        break;
      case ProvisioningActionEnum::CoGroupAdded:
        $template = EmailTemplateEnum::NewGroup;
        break;
      case ProvisioningActionEnum::CoGroupDeleted:
        $template = EmailTemplateEnum::RemoveGroup;
        break;
      case ProvisioningActionEnum::CoGroupUpdated:
      case ProvisioningActionEnum::CoGroupReprovisionRequested:
        $template = EmailTemplateEnum::UpdateGroup;
        break;
      default:
        throw new RuntimeException("Not Implemented");
        break;
    }

    // retrieve the template and determine all variables in it
    $template_object = $this->loadTemplate($coProvisioningTargetData['CoEmailProvisionerTarget'],$template);
    $vars = $this->parseVariables($template_object);

    // remove duplicate variables by making the template variables array keys
    $vars = array_flip($vars);
    $replacements=array();
    foreach($vars as $variable => $idx)
    {
        $replacements[$variable] = $this->extractData($variable, $provisioningData);
    }
    $template_object = $this->replaceVariables($template_object, $replacements);

    $this->sendEmail($coProvisioningTargetData['CoEmailProvisionerTarget']['adminaddress'],$template_object);

    return true;
  }

  /**
   * Load the indicated template for this Provisioner Target.
   *
   * @since: COManage Registry vTODO
   * @param: Array CoProvisioningTargetData
   * @param: EmailTemplateEnum enum
   */
  private function loadTemplate($target, $template)
  {
    $args = array();
    $args['conditions']['CoEmailProvisionerTemplate.co_email_provisioner_target_id'] = $target['id'];
    $args['conditions']['CoEmailProvisionerTemplate.template_type'] = $template;
    $args['contain'] = false; // retrieve all associated data
    $obj = $this->CoEmailProvisionerTemplate->find('first', $args);
    return $obj;
  }

  /**
   * Find all template variables in this specific template
   *
   * @since: COManage Registry vTODO
   * @param: Array CoEmailProvisionerTemplate
   */
  private function parseVariables($template)
  {
    // take precautions in case we pass templates from a Containable list (which has [CoEmailProvisionerTemplate][idx][values])
    // and directly from a model (which has [CoEmailProvisionerTemplate][values] indexing)
    $variables = array_unique(array_merge(
      $this->parseVariablesInText($template['CoEmailProvisionerTemplate']['subject']),
      $this->parseVariablesInText($template['CoEmailProvisionerTemplate']['message'])));
    return array_values($variables);
  }

  /**
   * Find all template variables (upper case letters enclosed in curly braces like:
   *     {TEMPLATEVAR} ) in this specific text
   *
   * @since: COManage Registry vTODO
   * @param: Array CoEmailProvisionerTemplate
   * @return: Array of template variables, excluding braces
   */
  private function parseVariablesInText($text)
  {
    $matches=array();
    $return_value=array();
    $count = preg_match_all("/\{([a-zA-Z][a-zA-Z0-9]+)\}/",$text,$matches, PREG_PATTERN_ORDER);
    if($count !== FALSE && $count > 0)
    {
      $return_value=$matches[1];
    }
    return $return_value;
  }

  /**
   * Replace all template variables with the provided replacements
   * Take care not to replace variables created due to previous replacements as per the PHP str_replace gotcha.
   * We do that by escaping all curly braces, replacing variables, and then unescaping the curly braces
   *
   * @since: COManage Registry vTODO
   * @param: Array CoEmailProvisionerTemplate
   * @param: Array of variables and their replacement value
   */
  private function replaceVariables($template, $replacements)
  {
    // put curly braces around all the vars
    $vars=array();
    foreach($replacements as $key=>$value) $vars["{".$key."}"]=str_replace(array("%","{","}"),array("%%","%{","%}"),$value);
    $template['CoEmailProvisionerTemplate']['subject'] = $this->replaceVariablesInText($template['CoEmailProvisionerTemplate']['subject'],array_keys($vars),array_values($vars));
    $template['CoEmailProvisionerTemplate']['message'] = $this->replaceVariablesInText($template['CoEmailProvisionerTemplate']['message'],array_keys($vars),array_values($vars));
    return $template;
  }

  /**
   * Replace all template variables with the provided replacements
   *
   * @since: COManage Registry vTODO
   * @param: Array CoEmailProvisionerTemplate
   * @param: Array of variables and their replacement value
   */
  private function replaceVariablesInText($text,$keys,$vars)
  {
    // escape all % signs, but not the curly braces in the text
    $text = str_replace(array("%"),array("%%"),$text);
    $text = str_replace($keys,$vars,$text);
    // unescape percentage signs and curly braces
    $text = str_replace(array("%%","%{","%}"),array("%","{","}"),$text);
    return $text;
  }

  /**
   * Extract the requested fields from the provisioningData
   *
   * @since: COManage Registry vTODO
   * @param: string template variable name
   * @param: Array Provisioning data, populated with ['CoPerson'] or ['CoGroup']
   */
  private function extractData($variable, $provisioningData)
  {
    $return_value="$variable"; // by default, do not replace the variable if we do not have a correct value for it
    $variable = strtoupper($variable);
    switch($variable)
    {
    case 'GIVENNAME':
      if(isset($provisioningData['PrimaryName']) && isset($provisioningData['PrimaryName']['given'])) {
        $return_value = $provisioningData['PrimaryName']['given'];
      }
      break;
    case 'SURNAME':
    case 'FAMILYNAME':
      if(isset($provisioningData['PrimaryName']) && isset($provisioningData['PrimaryName']['family'])) {
        $return_value = $provisioningData['PrimaryName']['family'];
      }
      break;
    case 'COMMONNAME':
    case 'NAME':
      if(!empty($provisioningData['CoPerson']['id'])) {
        $return_value = generateCn($provisioningData['PrimaryName']);
      } else {
        $return_value = $provisioningData['CoGroup']['name'];
      }
      break;
    // Attributes from CO Person Role
    case 'EDUPERSONAFFILIATION':
    case 'EMPLOYEETYPE':
    case 'O':
    case 'OU':
    case 'TITLE':
      if(isset($provisioningData['CoPersonRole'])) {
        $cols = array(
          'EDUPERSONAFFILIATION' => 'affiliation',
          'EMPLOYEETYPE' => 'affiliation',
          'O' => 'o',
          'OU' => 'ou',
          'TITLE' => 'title'
        );
        $return_value="";
        foreach($provisioningData['CoPersonRole'] as $r) {
          if(!empty($r[ $cols[$variable] ])) {
            if(strlen($return_value)) $return_value.=", ";
            $return_value .= $r[ $cols[$variable] ];
          }
        }
      }
      break;

    // Attributes from models attached to CO Person
    case 'EDUPERSONORCID':
    case 'EDUPERSONPRINCIPALNAME':
    case 'EDUPERSONPRINCIPALNAMEPRIOR':
    case 'EDUPERSONUNIQUEID':
    case 'EMPLOYEENUMBER':
    case 'MAIL':
    case 'UID':
      // Map the attribute to the model and column
      $mods = array(
        'EDUPERSONORCID' => 'Identifier',
        'EDUPERSONPRINCIPALNAME' => 'Identifier',
        'EDUPERSONPRINCIPALNAMEPRIOR' => 'Identifier',
        'EDUPERSONUNIQUEID' => 'Identifier',
        'EMPLOYEENUMBER' => 'Identifier',
        'MAIL' => 'EmailAddress',
        'UID' => 'Identifier'
      );

      $cols = array(
        'EDUPERSONORCID' => 'identifier',
        'EDUPERSONPRINCIPALNAME' => 'identifier',
        'EDUPERSONPRINCIPALNAMEPRIOR' => 'identifier',
        'EDUPERSONUNIQUEID' => 'identifier',
        'EMPLOYEENUMBER' => 'identifier',
        'MAIL' => 'mail',
        'UID' => 'identifier'
      );

      if(isset($provisioningData[ $mods[$variable] ])) {
        $modelList = $provisioningData[ $mods[$variable] ];
        if(isset($modelList)) {
          $return_value="";
          foreach($modelList as $m) {
            // If a type is set, make sure it matches
            if($variable != 'EDUPERSONORCID' || (IdentifierEnum::ORCID == $m['type'])) {
              // And finally that the attribute itself is set
              if(!empty($m[ $cols[$variable] ])) {
                if(strlen($return_value)) $return_value.=", ";
                $return_value .= $m[ $cols[$variable] ];
              }
            }
          }
        }
      }
      break;
    case 'SSHPUBLICKEY':
      if(isset($provisioningData['SshKey'])) {
        $return_value="";
        foreach($provisioningData['SshKey'] as $sk) {
          global $ssh_ti;
          if(strlen($return_value)) $return_value.="\r\n";
          $return_value .= $ssh_ti[ $sk['type'] ] . " " . $sk['skey'] . " " . $sk['comment'];
        }
      }
      break;
      // Attributes from models attached to CO Person Role
    case 'FACSIMILETELEPHONENUMBER':
    case 'FAX':
    case 'L':
    case 'MOBILE':
    case 'POSTALCODE':
    case 'ROOMNUMBER':
    case 'ST':
    case 'STREET':
    case 'TELEPHONENUMBER':
      // Map the attribute to the model and column
      $mods = array(
        'FACSIMILETELEPHONENUMBER' => 'TelephoneNumber',
        'FAX' => 'TelephoneNumber',
        'L' => 'Address',
        'MOBILE' => 'TelephoneNumber',
        'POSTALCODE' => 'Address',
        'ROOMNUMBER' => 'Address',
        'ST' => 'Address',
        'STREET' => 'Address',
        'TELEPHONENUMBER' => 'TelephoneNumber'
      );

      $cols = array(
        'FACSIMILETELEPHONENUMBER' => 'number',
        'FAX' => 'number',
        'L' => 'locality',
        'MOBILE' => 'number',
        'POSTALCODE' => 'postal_code',
        'ROOMNUMBER' => 'room',
        'ST' => 'state',
        'STREET' => 'street',
        'TELEPHONENUMBER' => 'number'
      );

      if(isset($provisioningData['CoPersonRole'])) {
        // Walk through each role, each of which can have more than one
        $return_value="";
        foreach($provisioningData['CoPersonRole'] as $r) {
          if(isset($r[ $mods[$variable] ])) {
            foreach($r[ $mods[$variable] ] as $m) {
              // Check that the attribute itself is set
              if(!empty($m[ $cols[$variable] ])) {
                if(strlen($return_value)) $return_value.=", ";
                if($mods[$variable] == 'TelephoneNumber') {
                  // Handle these specially... we want to format the number
                  // from the various components of the record
                    $return_value .= formatTelephone($m);
                } else {
                    $return_value .= $m[ $cols[$variable] ];
                }
              }
            }
          }
        }
      }
      break;
      // Group attributes (cn is covered above)
    case 'DESCRIPTION':
      // A blank description is invalid, so don't populate if empty
      if(!empty($provisioningData['CoGroup']['description'])) {
        $return_value = $provisioningData['CoGroup']['description'];
      }
      break;

      // HASMEMBER returns all members of the currently provisioned group
    case 'MEMBERS':
    case 'HASMEMBER':
      if(!empty($provisioningData['CoGroup']['id'])) {
        $memberModel = ClassRegistry::init('CoGroupMember');
        if(isset($memberModel)) {
          $args = array();
          $args['conditions']['CoGroupMember.co_group_id'] = $provisioningData['CoGroup']['id'];
          $args['contain'] = false;
          $groupMembers = $memberModel->find('all', $args);
          $members = $memberModel->mapCoGroupMembersToIdentifiers($groupMembers, IdentifierEnum::ePPN);
          $return_value="";
          if(!empty($members)) {
            $return_value=implode(", ",$members);
          }
        }
      }
      break;

      // ISMEMBEROF returns all groups the currently provisioned user is a member of
    case 'ISMEMBEROF':
      if(!empty($provisioningData['CoPerson']['id'])) {
        if(!empty($provisioningData['CoGroupMember'])) {
          $return_value="";
          foreach($provisioningData['CoGroupMember'] as $gm) {
            if(isset($gm['member']) && $gm['member']
              && !empty($gm['CoGroup']['name'])) {
              if(strlen($return_value)) $return_value.=", ";
              $return_value.= $gm['CoGroup']['name'];
            }
          }
        }
      }
      break;

      // eduPersonEntitlement is based on Group memberships
    case 'EDUPERSONENTITLEMENT':
      if(!empty($provisioningData['CoPerson']['id'])) {
        if(!empty($provisioningData['CoGroupMember'])) {
          $entGroupIds = Hash::extract($provisioningData['CoGroupMember'], '{n}.co_group_id');
          $return_value = implode(",", $this->CoProvisioningTarget->Co->CoGroup->CoService
                                              ->mapCoGroupsToEntitlements($provisioningData['Co']['id'], $entGroupIds));
        }
      }
      break;
      // posixAccount attributes
    case 'GECOS':
      // Construct using same name as cn
      if(isset($provisioningData['PrimaryName'])) {
        $return_value = generateCn($provisioningData['PrimaryName']) . ",,,";
      }
      break;
    case 'GIDNUMBER':
    case 'HOMEDIRECTORY':
    case 'UIDNUMBER':
      // We pull these attributes from Identifiers with types of the same name
      // as an experimental implementation for CO-863.
      if(isset($provisioningData['Identifier'])) {
        foreach($provisioningData['Identifier'] as $m) {
          if(isset($m['type'])
            && strtoupper($m['type']) == $variable
            && $m['status'] == StatusEnum::Active) {
            $return_value = $m['identifier'];
            break;
          }
        }
      }
      break;
    }
    return $return_value;
  }

  /**
   * Send a template email to the indicated address(es)
   *
   * @since: COManage Registry vTODO
   * @param: string admin address(es), comma separated if multiple
   * @param: Array CoEmailProvisionerTemplate
   */
  private function sendEmail($address, $template)
  {
    // Set up and send the email using the provided templates. We do not create
    // a history record, as the admin email addresses to send to might not even be in the
    // registry.
    $email = $this->getEmailer();

    try {
      $msgSubject = $template['CoEmailProvisionerTemplate']['subject'];
      $msgBody = $template['CoEmailProvisionerTemplate']['message'];
      if(strlen($msgBody) || strlen($msgSubject)) {
        $email->emailFormat('text')
              ->to($address)
              ->subject($msgSubject);
        $email->send($msgBody);
      }
    } catch(Exception $e) {
      throw new RuntimeException($e->getMessage());
    }
  }

  // private getter for the email object to allow dependency injection during unit tests
  private function getEmailer() {
    if(!isset($this->_emailer))  $this->_emailer = new CakeEmail();
    return $this->_emailer;
  }

  public function saveAll($data)
  {
    parent::saveAll($data);
    // the edit page does not implement a containable interface, which would allow us to save all
    // contained fields in one go. Instead we display 6 specific fields, wether they exist or not
    // in the database. We need to save those template fields now, even if empty.
    foreach(array('N','E','R','C','U','D') as $i) {
      $template_id = isset($data['CoEmailProvisionerTarget']['message_'.$i.'_id']) ?  intval($data['CoEmailProvisionerTarget']['message_'.$i.'_id']) : -1;
      $message = isset($data['CoEmailProvisionerTarget']["message_$i"]) ? $data['CoEmailProvisionerTarget']["message_$i"] : '';
      $subject = isset($data['CoEmailProvisionerTarget']["message_subject_$i"]) ? $data['CoEmailProvisionerTarget']["message_subject_$i"] : '';
      $template = null;
      if($template_id > 0) {
        $template = $this->CoEmailProvisionerTemplate->read(null, $template_id);
      }
      if($template === null || sizeof($template) == 0) {
        $template = $this->CoEmailProvisionerTemplate->create(array(
          "co_email_provisioner_target_id" => $this->id,
          "created" => date('Y-m-d H:i:s')
        ));
        switch($i)
        {
        default:
        case 'N': $this->CoEmailProvisionerTemplate->set('template_type',EmailTemplateEnum::NewPerson); break;
        case 'E': $this->CoEmailProvisionerTemplate->set('template_type',EmailTemplateEnum::UpdatePerson); break;
        case 'R': $this->CoEmailProvisionerTemplate->set('template_type',EmailTemplateEnum::RemovePerson); break;
        case 'C': $this->CoEmailProvisionerTemplate->set('template_type',EmailTemplateEnum::NewGroup); break;
        case 'U': $this->CoEmailProvisionerTemplate->set('template_type',EmailTemplateEnum::UpdateGroup); break;
        case 'D': $this->CoEmailProvisionerTemplate->set('template_type',EmailTemplateEnum::RemoveGroup); break;
        }
      }
      $this->CoEmailProvisionerTemplate->set(array(
        "subject" => $subject,
        "message" => $message,
        "modified" => date('Y-m-d H:i:s'),
      ));

      $this->CoEmailProvisionerTemplate->save();
    }
    return true;
  }
}
