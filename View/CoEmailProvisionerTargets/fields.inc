<!--
/**
 * COmanage Registry CO Email Provisioning Target Fields
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
 * @since         COmanage Registry v0.8
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */
-->
<style>
ul.form-list textarea {
    width: 97%;
  }
</style>
<script type="text/javascript">
  $(function() {
    // Error dialog

    $("#error-dialog").dialog({
      autoOpen: false,
      buttons: {
        "<?php print _txt('op.ok'); ?>": function() {
          $(this).dialog("close");
        },
      },
      modal: true,
      show: {
        effect: "fade"
      },
      hide: {
        effect: "fade"
      }
    });
  });
</script>
<?php
  // Determine if fields are editable
  $e = false;

  if(($this->action == "add" && $permissions['add']) || ($this->action == "edit" && $permissions['edit']))
    $e = true;

  // We shouldn't get here if we don't have at least read permission, but check just in case

  if(!$e && !$permissions['view'])
    return false;

  print $this->Form->hidden('co_id', array('default' => $cur_co['Co']['id'])) . "\n";
  print $this->Form->hidden('co_provisioning_target_id', array('default' => $vv_ptid)) . "\n";

  $template_values = array(
    EmailTemplateEnum::NewPerson => array ("id" => -1,
                                           "subject"=>_txt('pl.emailprovisioner.template_newuser_subject.default'),
                                           "message"=>_txt('pl.emailprovisioner.template_newuser_template.default')),
    EmailTemplateEnum::UpdatePerson => array ("id" => -1,
                                           "subject"=>_txt('pl.emailprovisioner.template_updateuser_subject.default'),
                                           "message"=>_txt('pl.emailprovisioner.template_updateuser_template.default')),
    EmailTemplateEnum::RemovePerson => array ("id" => -1,
                                           "subject"=>_txt('pl.emailprovisioner.template_removeuser_subject.default'),
                                           "message"=>_txt('pl.emailprovisioner.template_removeuser_template.default')),
    EmailTemplateEnum::NewGroup => array ("id" => -1,
                                           "subject"=>_txt('pl.emailprovisioner.template_newgroup_subject.default'),
                                           "message"=>_txt('pl.emailprovisioner.template_newgroup_template.default')),
    EmailTemplateEnum::UpdateGroup => array ("id" => -1,
                                           "subject"=>_txt('pl.emailprovisioner.template_updategroup_subject.default'),
                                           "message"=>_txt('pl.emailprovisioner.template_updategroup_template.default')),
    EmailTemplateEnum::RemoveGroup => array ("id" => -1,
                                           "subject"=>_txt('pl.emailprovisioner.template_removegroup_subject.default'),
                                           "message"=>_txt('pl.emailprovisioner.template_removegroup_template.default'))
  );
  foreach($templates as $template) {
      $type = $template['CoEmailProvisionerTemplate']['template_type'];
      $template_values[ $type ]['id'] = $template['CoEmailProvisionerTemplate']['id'];
      $template_values[ $type ]['message'] = $template['CoEmailProvisionerTemplate']['message'];
      $template_values[ $type ]['subject'] = $template['CoEmailProvisionerTemplate']['subject'];
  }

?>
<div class="co-info-topbox">
  <i class="material-icons">info</i>
  <?php print _txt('pl.emailprovisioner.info'); ?>
</div>

<ul id="<?php print $this->action; ?>_co_email_provisioner_target" class="fields form-list form-list-admin">
  <li>
    <div class="field-name">
      <div class="field-title"><?php print _txt('pl.emailprovisioner.adminaddress'); ?> <span class="required">*</span></div>
      <div class="field-desc"><?php print _txt('pl.emailprovisioner.adminaddress.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('adminaddress', array('size' => 50)) : filter_var($co_adminaddress_provisioner_targets[0]['CoEmailProvisionerTarget']['adminaddress'],FILTER_SANITIZE_EMAIL)); ?>
    </div>
  </li>

  <li>
    <div class="field-name vtop">
      <div class="field-title"><?php print _txt('pl.emailprovisioner.template_newuser_subject'); ?> </div>
      <div class="field-desc"><?php print _txt('pl.emailprovisioner.template_newuser_subject.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e
             ? $this->Form->input('message_subject_N', array('type'=>'text', "default"=>$template_values[EmailTemplateEnum::NewPerson]['subject']))
             : filter_var($template_values[EmailTemplateEnum::NewPerson]['subject'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name vtop">
      <div class="field-title"><?php print _txt('pl.emailprovisioner.template_newuser_template'); ?> </div>
      <div class="field-desc"><?php print _txt('pl.emailprovisioner.template_newuser_template.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print $this->Form->hidden('message_N_id', array('default' => $template_values[EmailTemplateEnum::NewPerson]['id'])) . "\n";
            print ($e
             ? $this->Form->input('message_N', array('type'=>'textarea', "value"=>$template_values[EmailTemplateEnum::NewPerson]['message']))
             : filter_var($template_values[EmailTemplateEnum::NewPerson]['message'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>

  <li>
    <div class="field-name vtop">
      <div class="field-title"><?php print _txt('pl.emailprovisioner.template_updateuser_subject'); ?></div>
      <div class="field-desc"><?php print _txt('pl.emailprovisioner.template_updateuser_subject.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e
             ? $this->Form->input('message_subject_E', array('type'=>'text', "default"=>$template_values[EmailTemplateEnum::UpdatePerson]['subject']))
             : filter_var($template_values[EmailTemplateEnum::UpdatePerson]['subject'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name vtop">
      <div class="field-title"><?php print _txt('pl.emailprovisioner.template_updateuser_template'); ?> </div>
      <div class="field-desc"><?php print _txt('pl.emailprovisioner.template_updateuser_template.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print $this->Form->hidden('message_E_id', array('default' => $template_values[EmailTemplateEnum::UpdatePerson]['id'])) . "\n";
            print ($e
             ? $this->Form->input('message_E', array('type'=>'textarea', "value"=>$template_values[EmailTemplateEnum::UpdatePerson]['message']))
             : filter_var($template_values[EmailTemplateEnum::UpdatePerson]['message'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>

  <li>
    <div class="field-name vtop">
      <div class="field-title"><?php print _txt('pl.emailprovisioner.template_removeuser_subject'); ?> </div>
      <div class="field-desc"><?php print _txt('pl.emailprovisioner.template_removeuser_subject.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e
             ? $this->Form->input('message_subject_R', array('type'=>'text', "default"=>$template_values[EmailTemplateEnum::RemovePerson]['subject']))
             : filter_var($template_values[EmailTemplateEnum::RemovePerson]['subject'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name vtop">
      <div class="field-title"><?php print _txt('pl.emailprovisioner.template_removeuser_template'); ?> </div>
      <div class="field-desc"><?php print _txt('pl.emailprovisioner.template_removeuser_template.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print $this->Form->hidden('message_R_id', array('default' => $template_values[EmailTemplateEnum::RemovePerson]['id'])) . "\n";
            print ($e
             ? $this->Form->input('message_R', array('type'=>'textarea', "value"=>$template_values[EmailTemplateEnum::RemovePerson]['message']))
             : filter_var($template_values[EmailTemplateEnum::RemovePerson]['message'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>

  <li>
    <div class="field-name vtop">
      <div class="field-title"><?php print _txt('pl.emailprovisioner.template_newgroup_subject'); ?> </div>
      <div class="field-desc"><?php print _txt('pl.emailprovisioner.template_newgroup_subject.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e
             ? $this->Form->input('message_subject_C', array('type'=>'text', "default"=>$template_values[EmailTemplateEnum::NewGroup]['subject']))
             : filter_var($template_values[EmailTemplateEnum::NewGroup]['subject'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name vtop">
      <div class="field-title"><?php print _txt('pl.emailprovisioner.template_newgroup_template'); ?> </div>
      <div class="field-desc"><?php print _txt('pl.emailprovisioner.template_newgroup_template.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print $this->Form->hidden('message_C_id', array('default' => $template_values[EmailTemplateEnum::NewGroup]['id'])) . "\n";
            print ($e
             ? $this->Form->input('message_C', array('type'=>'textarea', "value"=>$template_values[EmailTemplateEnum::NewGroup]['message']))
             : filter_var($template_values[EmailTemplateEnum::NewGroup]['message'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>

  <li>
    <div class="field-name vtop">
      <div class="field-title"><?php print _txt('pl.emailprovisioner.template_updategroup_subject'); ?> </div>
      <div class="field-desc"><?php print _txt('pl.emailprovisioner.template_updategroup_subject.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e
             ? $this->Form->input('message_subject_U', array('type'=>'text', "default"=>$template_values[EmailTemplateEnum::UpdateGroup]['subject']))
             : filter_var($template_values[EmailTemplateEnum::UpdateGroup]['subject'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name vtop">
      <div class="field-title"><?php print _txt('pl.emailprovisioner.template_updategroup_template'); ?> </div>
      <div class="field-desc"><?php print _txt('pl.emailprovisioner.template_updategroup_template.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print $this->Form->hidden('message_U_id', array('default' => $template_values[EmailTemplateEnum::UpdateGroup]['id'])) . "\n";
            print ($e
             ? $this->Form->input('message_U', array('type'=>'textarea', "value"=>$template_values[EmailTemplateEnum::UpdateGroup]['message']))
             : filter_var($template_values[EmailTemplateEnum::UpdateGroup]['message'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>

  <li>
    <div class="field-name vtop">
      <div class="field-title"><?php print _txt('pl.emailprovisioner.template_removegroup_subject'); ?> </div>
      <div class="field-desc"><?php print _txt('pl.emailprovisioner.template_removegroup_subject.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e
             ? $this->Form->input('message_subject_D', array('type'=>'text', "default"=>$template_values[EmailTemplateEnum::RemoveGroup]['subject']))
             : filter_var($template_values[EmailTemplateEnum::RemoveGroup]['subject'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name vtop">
      <div class="field-title"><?php print _txt('pl.emailprovisioner.template_removegroup_template'); ?> </div>
      <div class="field-desc"><?php print _txt('pl.emailprovisioner.template_removegroup_template.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print $this->Form->hidden('message_D_id', array('default' => $template_values[EmailTemplateEnum::RemoveGroup]['id'])) . "\n";
            print ($e
             ? $this->Form->input('message_D', array('type'=>'textarea', "value"=>$template_values[EmailTemplateEnum::RemoveGroup]['message']))
             : filter_var($template_values[EmailTemplateEnum::RemoveGroup]['message'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <?php if($e): ?>
    <li class="fields-submit">
      <div class="field-desc">
        <?php print _txt('pl.emailprovisioner.template_variables.info'); ?>
      </div>
      <div class="field-info">
        <?php print $this->Form->submit($submit_label); ?>
      </div>
      <div>
        <span class="required"><?php print _txt('fd.req'); ?></span>
      </div>
    </li>
  <?php endif; ?>
</ul>
