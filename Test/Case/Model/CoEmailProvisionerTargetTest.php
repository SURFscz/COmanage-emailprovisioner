<?php

App::uses('Model', 'Model');
App::uses('Controller', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class CoEmailProvisionerTargetTest extends CakeTestCase {

  public $useDbConfig=false;

  public $fixtures = array(
	'plugin.emailProvisioner.coprovisioningtarget',
	'plugin.emailProvisioner.coemailprovisionertarget',
	'plugin.emailProvisioner.coemailprovisionertemplate',
	"plugin.emailProvisioner.co",
	"plugin.emailProvisioner.cogroup",
	"plugin.emailProvisioner.consfdemographic",
	"plugin.emailProvisioner.coinvite",
	"plugin.emailProvisioner.conotification",
	"plugin.emailProvisioner.orgidentity",
	"plugin.emailProvisioner.coorgidentitylink",
	"plugin.emailProvisioner.copersonrole",
	"plugin.emailProvisioner.copetition",
	"plugin.emailProvisioner.copetitionhistoryrecord",
	"plugin.emailProvisioner.cotandcagreement",
	"plugin.emailProvisioner.emailaddress",
	"plugin.emailProvisioner.historyrecord",
	"plugin.emailProvisioner.coprovisioningexport",
	"plugin.emailProvisioner.sshkey",
	"plugin.emailProvisioner.cou",
	"plugin.emailProvisioner.coenrollmentflow",
	"plugin.emailProvisioner.coexpirationpolicy",
	"plugin.emailProvisioner.cosetting",
	"plugin.emailProvisioner.coservice",
	"plugin.emailProvisioner.name",
	"plugin.emailProvisioner.coperson",
	"plugin.emailProvisioner.identifier",
	"plugin.emailProvisioner.cogroupmember",
	"plugin.emailProvisioner.telephone",
	"plugin.emailProvisioner.address",
  );

  public $CEPT;

  public function startTest($method) {
	$this->CEPT = ClassRegistry::init('EmailProvisioner.CoEmailProvisionerTarget');
    $this->CEPTemplate = ClassRegistry::init('EmailProvisioner.CoEmailProvisionerTemplate');
    $this->CPT = ClassRegistry::init('CoProvisioningTarget');
    $this->CP = ClassRegistry::init('CoPerson');
    $this->CG = ClassRegistry::init('CoGroup');
  }

  public function endTest($method) {
	unset($this->CEPT);
  }

  protected static function getMethod($obj, $name) {
    $class = new ReflectionClass(get_class($obj));
    $method = $class->getMethod($name);
    $method->setAccessible(true);
    return $method;
  }

  public function testExtractData() {
    $method = $this->getMethod($this->CEPT,"extractData");
    // TODO: incorporate telephone and address
    $this->CP->contain(array('SshKey','Co','CoOrgIdentityLink'=>array('OrgIdentity'=>array('Address','PrimaryName','TelephoneNumber')),'PrimaryName', 'EmailAddress', 'Identifier','Name','SshKey','CoPersonRole'=>array('Address','TelephoneNumber'),'CoGroupMember'=>'CoGroup'));
    $person1 = $this->CP->find('first',array('conditions'=>array("CoPerson.id"=>1)));
    $group3 = $this->CG->find('first',array('conditions'=>array("CoGroup.id"=>3)));

    $expectedResultsPerson = array(
      "GIVENNAME" => "such",
      "SURNAME" => "name",
      "FAMILYNAME" => "name",
      "COMMONNAME" => "such name ever",
      "NAME" => "such name ever",
      "EDUPERSONAFFILIATION" => "staff",
      "EMPLOYEETYPE" => "staff",
      "O" => "Antartica Mining Corp",
      "OU" => "Sales",
      "TITLE" => "Secretary",
      "EDUPERSONORCID" => "orcid-identifier",
      "EDUPERSONPRINCIPALNAME" => "test@example.com, test2@example.com, 1002, /home/example, 1001, orcid-identifier",
      "EDUPERSONPRINCIPALNAMEPRIOR" => "test@example.com, test2@example.com, 1002, /home/example, 1001, orcid-identifier",
      "EDUPERSONUNIQUEID" => "test@example.com, test2@example.com, 1002, /home/example, 1001, orcid-identifier",
      "MAIL" => "nosuchmail@example.com, testemail@example.com",
      "UID" => "test@example.com, test2@example.com, 1002, /home/example, 1001, orcid-identifier",
      "SSHPUBLICKEY" => "ssh-rsa Bogus Key #empty comment",
      "FACSIMILETELEPHONENUMBER" => "+00 911 112 x8484",
      "FAX" => "+00 911 112 x8484",
      "L" => "Here",
      "MOBILE" => "+00 911 112 x8484",
      "POSTALCODE" => "AN 455",
      "ROOMNUMBER" => "101",
      "ST" => "There",
      "STREET" => "Street 12\r\nLine 2",
      "TELEPHONENUMBER" => "+00 911 112 x8484",
      "DESCRIPTION" => "DESCRIPTION",
      "MEMBERS" => "MEMBERS",
      "HASMEMBER" => "HASMEMBER",
      "ISMEMBEROF" => "CO:admins, CO:members:active, CO:members",
      "EDUPERSONENTITLEMENT" => "entitlement:for:test:purposes.org:more",
      "GECOS" => "such name ever,,,",
      "GIDNUMBER" => "1002",
      "HOMEDIRECTORY" => "/home/example",
      "UIDNUMBER" => "1001"
    );
    foreach($expectedResultsPerson as $key=>$val) {
      $return_value=$method->invokeArgs($this->CEPT, array($key,$person1));
      $this->assertTextEquals($val,$return_value,"Expected different result for variable '$key'");
    }

    $expectedResultsGroup = array(
      "GIVENNAME" => "GIVENNAME",
      "SURNAME" => "SURNAME",
      "FAMILYNAME" => "FAMILYNAME",
      "COMMONNAME" => "CO:members",
      "NAME" => "CO:members",
      "EDUPERSONAFFILIATION" => "EDUPERSONAFFILIATION",
      "EMPLOYEETYPE" => "EMPLOYEETYPE",
      "O" => "O",
      "OU" => "OU",
      "TITLE" => "TITLE",
      "EDUPERSONORCID" => "EDUPERSONORCID",
      "EDUPERSONPRINCIPALNAME" => "EDUPERSONPRINCIPALNAME",
      "EDUPERSONPRINCIPALNAMEPRIOR" => "EDUPERSONPRINCIPALNAMEPRIOR",
      "EDUPERSONUNIQUEID" => "EDUPERSONUNIQUEID",
      "MAIL" => "MAIL",
      "UID" => "UID",
      "SSHPUBLICKEY" => "SSHPUBLICKEY",
      "FACSIMILETELEPHONENUMBER" => "FACSIMILETELEPHONENUMBER",
      "FAX" => "FAX",
      "L" => "L",
      "MOBILE" => "MOBILE",
      "POSTALCODE" => "POSTALCODE",
      "ROOMNUMBER" => "ROOMNUMBER",
      "ST" => "ST",
      "STREET" => "STREET",
      "TELEPHONENUMBER" => "TELEPHONENUMBER",
      "DESCRIPTION" => "COmanage Members",
      "MEMBERS" => "test2@example.com",
      "HASMEMBER" => "test2@example.com",
      "ISMEMBEROF" => "ISMEMBEROF",
      "EDUPERSONENTITLEMENT" => "EDUPERSONENTITLEMENT",
      "GECOS" => "GECOS",
      "GIDNUMBER" => "GIDNUMBER",
      "HOMEDIRECTORY" => "HOMEDIRECTORY",
      "UIDNUMBER" => "UIDNUMBER"
    );
    foreach($expectedResultsGroup as $key=>$val) {
      $return_value=$method->invokeArgs($this->CEPT, array($key,$group3));
      $this->assertTextEquals($val,$return_value,"Expected different result for variable '$key'");
    }

  }

  public function testEmailException() {
    $this->CEPT->_emailer=new TestEmail();
    $this->CEPT->_emailer->transport('Debug');
    $method = $this->getMethod($this->CEPT,"sendEmail");
    $this->expectException("RuntimeException");
    $method->invokeArgs($this->CEPT,array(null,array()));
  }

  private function filterHeaders($content) {
    // filter only the subject and To headers
    $to="";
    $subject="";
    $matches=array();
    $cnt=preg_match("/[^a-zA-Z0-9]To:[ \t]*([^\r\n]*)/",$content['headers'],$matches);
    if($cnt>0) {
      $to = $matches[1];
    }
    $cnt=preg_match("/[^a-zA-Z0-9]Subject:[ \t]*([^\r\n]*)/",$content['headers'],$matches);
    if($cnt>0) {
      $subject = $matches[1];
    }
    return $to."\r\n".$subject;
  }

  public function testProvision() {
    $target = $this->CEPT->find('first',array("conditions"=>array("CoEmailProvisionerTarget.id"=>3)));
    $this->CEPT->_emailer=new TestEmail();
    $this->CEPT->_emailer->transport('Debug');

    $statuses = array(ProvisioningActionEnum::CoGroupAdded,ProvisioningActionEnum::CoGroupDeleted,ProvisioningActionEnum::CoGroupReprovisionRequested,
      ProvisioningActionEnum::CoGroupUpdated,ProvisioningActionEnum::CoPersonAdded,ProvisioningActionEnum::CoPersonDeleted,ProvisioningActionEnum::CoPersonEnteredGracePeriod,
      ProvisioningActionEnum::CoPersonExpired,ProvisioningActionEnum::CoPersonPetitionProvisioned,ProvisioningActionEnum::CoPersonPipelineProvisioned,
      ProvisioningActionEnum::CoPersonReprovisionRequested,ProvisioningActionEnum::CoPersonUnexpired,ProvisioningActionEnum::CoPersonUpdated);

    $expectedPerson1hashes=array(
      ProvisioningActionEnum::CoGroupAdded => "5c434545a0199680f58447cba3d2218ec5881550", // new group template
      ProvisioningActionEnum::CoGroupDeleted => "bcafc23d9084eeadf08cd05954380ca807f28cda", // remove group template
      ProvisioningActionEnum::CoGroupReprovisionRequested => "ad929cdec990ee9a1595a9019b1b7a819cf3b1a8", // update group template
      ProvisioningActionEnum::CoGroupUpdated =>"ad929cdec990ee9a1595a9019b1b7a819cf3b1a8",
      ProvisioningActionEnum::CoPersonAdded =>"5d620cd3cdbbfe36b2b734f62e172d15c9bdf41f", // new person template
      ProvisioningActionEnum::CoPersonDeleted =>"739284c6648389d7f7bf9c43425838c34a881db8", // remove person template
      ProvisioningActionEnum::CoPersonEnteredGracePeriod =>"d4ac81cf6df1b2b3c9664f5b21a8091e304c66c7", // update person template
      ProvisioningActionEnum::CoPersonExpired =>"d4ac81cf6df1b2b3c9664f5b21a8091e304c66c7",
      ProvisioningActionEnum::CoPersonPetitionProvisioned =>"d4ac81cf6df1b2b3c9664f5b21a8091e304c66c7",
      ProvisioningActionEnum::CoPersonPipelineProvisioned =>"d4ac81cf6df1b2b3c9664f5b21a8091e304c66c7",
      ProvisioningActionEnum::CoPersonReprovisionRequested =>"d4ac81cf6df1b2b3c9664f5b21a8091e304c66c7",
      ProvisioningActionEnum::CoPersonUnexpired =>"d4ac81cf6df1b2b3c9664f5b21a8091e304c66c7",
      ProvisioningActionEnum::CoPersonUpdated =>"d4ac81cf6df1b2b3c9664f5b21a8091e304c66c7"
    );

    $person1 = $this->CP->find('first',array('conditions'=>array("CoPerson.id"=>1)));
    foreach($statuses as $op)
    {
      $this->CEPT->provision($target, $op, $person1);
      $hash=sha1($this->filterHeaders($this->CEPT->_emailer->_debug_content) . trim($this->CEPT->_emailer->_debug_content['message']));
      $this->assertTextEquals($expectedPerson1hashes[$op],$hash,"Expected different has for operation '$op'");
    }

    // person5 has status Approved, which is not sufficient for provisioning, so several states are
    // changed to a delete instead of an update operation
    $expectedPerson5hashes=array(
      ProvisioningActionEnum::CoGroupAdded => "a76e74d42554390e2e3ec70ebf839bb7eb86bb7e",
      ProvisioningActionEnum::CoGroupDeleted => "a04d46d0c669a6b7d019edca070a9cbacfa70b71",
      ProvisioningActionEnum::CoGroupReprovisionRequested => "1de43583693c830df7c3961e78fef6c417905fd7",
      ProvisioningActionEnum::CoGroupUpdated =>"1de43583693c830df7c3961e78fef6c417905fd7",
      ProvisioningActionEnum::CoPersonAdded =>"c7f08bea53ff18ccf9411e8350ddcf852e0e6dcc",
      ProvisioningActionEnum::CoPersonDeleted =>"3460cb396900554972b8058d58edc5b7e265191f",// remove person template
      ProvisioningActionEnum::CoPersonEnteredGracePeriod =>"3460cb396900554972b8058d58edc5b7e265191f",
      ProvisioningActionEnum::CoPersonExpired =>"3460cb396900554972b8058d58edc5b7e265191f",
      ProvisioningActionEnum::CoPersonPetitionProvisioned =>"58edec105085f153e8f7fc83be23a02fe6be4c27",// update person template
      ProvisioningActionEnum::CoPersonPipelineProvisioned =>"58edec105085f153e8f7fc83be23a02fe6be4c27",
      ProvisioningActionEnum::CoPersonReprovisionRequested =>"58edec105085f153e8f7fc83be23a02fe6be4c27",
      ProvisioningActionEnum::CoPersonUnexpired =>"58edec105085f153e8f7fc83be23a02fe6be4c27",
      ProvisioningActionEnum::CoPersonUpdated =>"3460cb396900554972b8058d58edc5b7e265191f"
    );
    $person5 = $this->CP->find('first',array('conditions'=>array("CoPerson.id"=>5)));
    foreach($statuses as $op)
    {
      $this->CEPT->provision($target, $op, $person5);
      $hash=sha1($this->filterHeaders($this->CEPT->_emailer->_debug_content) . trim($this->CEPT->_emailer->_debug_content['message']));
      $this->assertTextEquals($expectedPerson5hashes[$op],$hash,"Expected different has for operation '$op'");
    }

    $expectedGroup3hashes=array(
      ProvisioningActionEnum::CoGroupAdded => "a76e74d42554390e2e3ec70ebf839bb7eb86bb7e",
      ProvisioningActionEnum::CoGroupDeleted => "a04d46d0c669a6b7d019edca070a9cbacfa70b71",
      ProvisioningActionEnum::CoGroupReprovisionRequested => "1de43583693c830df7c3961e78fef6c417905fd7",
      ProvisioningActionEnum::CoGroupUpdated =>"1de43583693c830df7c3961e78fef6c417905fd7",
      ProvisioningActionEnum::CoPersonAdded =>"c7f08bea53ff18ccf9411e8350ddcf852e0e6dcc",
      ProvisioningActionEnum::CoPersonDeleted =>"3460cb396900554972b8058d58edc5b7e265191f",
      ProvisioningActionEnum::CoPersonEnteredGracePeriod =>"58edec105085f153e8f7fc83be23a02fe6be4c27",
      ProvisioningActionEnum::CoPersonExpired =>"58edec105085f153e8f7fc83be23a02fe6be4c27",
      ProvisioningActionEnum::CoPersonPetitionProvisioned =>"58edec105085f153e8f7fc83be23a02fe6be4c27",
      ProvisioningActionEnum::CoPersonPipelineProvisioned =>"58edec105085f153e8f7fc83be23a02fe6be4c27",
      ProvisioningActionEnum::CoPersonReprovisionRequested =>"58edec105085f153e8f7fc83be23a02fe6be4c27",
      ProvisioningActionEnum::CoPersonUnexpired =>"58edec105085f153e8f7fc83be23a02fe6be4c27",
      ProvisioningActionEnum::CoPersonUpdated =>"58edec105085f153e8f7fc83be23a02fe6be4c27"
    );
    $group3 = $this->CG->find('first',array('conditions'=>array("CoGroup.id"=>3)));
    foreach($statuses as $op)
    {
      $this->CEPT->provision($target, $op, $person5);
      $hash=sha1($this->filterHeaders($this->CEPT->_emailer->_debug_content) . trim($this->CEPT->_emailer->_debug_content['message']));
      $this->assertTextEquals($expectedPerson5hashes[$op],$hash,"Expected different has for operation '$op'");
    }

    // finally test that unknown operators end up with an exception
    $this->expectException("RuntimeException","Not Implemented");
    $this->CEPT->provision($target, "NO SUCH OP", $person5);
  }

  public function testLoadTemplate() {
    $method = $this->getMethod($this->CEPT,"loadTemplate");
    $template=$method->invokeArgs($this->CEPT, array(array('id'=>1),EmailTemplateEnum::NewPerson));
    $this->assertTextEquals('{"id":"1","subject":"Subject for New Person","message":"Template for New Person","created":"1999-12-11 11:23:45","modified":"1999-12-11 11:23:45","template_type":"N","co_email_provisioner_target_id":"1"}',json_encode($template['CoEmailProvisionerTemplate']),"Expected different template");
    $template=$method->invokeArgs($this->CEPT, array(array('id'=>1),EmailTemplateEnum::NewGroup));
    $this->assertTextEquals('{"id":"2","subject":"Subject for New Group","message":"Template for New Group","created":"1999-12-11 11:23:45","modified":"1999-12-11 11:23:45","template_type":"C","co_email_provisioner_target_id":"1"}',json_encode($template['CoEmailProvisionerTemplate']),"Expected different template");
    $template=$method->invokeArgs($this->CEPT, array(array('id'=>1),EmailTemplateEnum::UpdatePerson));
    $this->assertTextEquals('[]',json_encode($template),"Expected different template");
    $template=$method->invokeArgs($this->CEPT, array(array('id'=>2),EmailTemplateEnum::NewPerson));
    $this->assertTextEquals('{"id":"3","subject":"Subject with {VAR} and {name}","message":"{VAR} in a {{template} {NAME}}","created":"1999-12-11 11:23:45","modified":"1999-12-11 11:23:45","template_type":"N","co_email_provisioner_target_id":"2"}',json_encode($template['CoEmailProvisionerTemplate']),"Expected different template");
    $template=$method->invokeArgs($this->CEPT, array(array('id'=>2),EmailTemplateEnum::NewGroup));
    $this->assertTextEquals('{"id":"4","subject":"{{{{0NOVAR","message":"{ADDRESS} {NAME} {GROUP}{template}","created":"1999-12-11 11:23:45","modified":"1999-12-11 11:23:45","template_type":"C","co_email_provisioner_target_id":"2"}',json_encode($template['CoEmailProvisionerTemplate']),"Expected different template");
    $template=$method->invokeArgs($this->CEPT, array(array('id'=>2),EmailTemplateEnum::UpdatePerson));
    $this->assertTextEquals('[]',json_encode($template),"Expected different template");
  }

  public function testReplaceVariables() {
    $method = $this->getMethod($this->CEPT,"replaceVariables");
    // test passing a template directly
    $templ3= $this->CEPTemplate->find('first',array(
        'conditions' => array('CoEmailProvisionerTemplate.id' => 3)));
    $template=$method->invokeArgs($this->CEPT, array($templ3,array(
        "VAR"=>"{NAME}",
        "name"=>"principal name",
        "template"=>"{UNUSED}",
//        "NAME" => "",
        "UNUSED" => "",
      )));
    $this->assertTextEquals('"Subject with {NAME} and principal name"',json_encode($template['CoEmailProvisionerTemplate']['subject']),"Expected different variables");
    $this->assertTextEquals('"{NAME} in a {{UNUSED} {NAME}}"', json_encode($template['CoEmailProvisionerTemplate']['message']),"Expected different variables");
    $templ4= $this->CEPTemplate->find('first',array(
        'conditions' => array('CoEmailProvisionerTemplate.id' => 4)));
    $template=$method->invokeArgs($this->CEPT, array($templ4, array(
        "ADDRESS" => "{{address",
        "NAME" => "GROUP}}",
        "GROUP" => "{template}",
        "template" => "{GROUP}",
        "no such variable" => "with content"
      )));
    $this->assertTextEquals('"{{{{0NOVAR"',json_encode($template['CoEmailProvisionerTemplate']['subject']),"Expected different variables");
    $this->assertTextEquals('"{{address GROUP}} {template}{GROUP}"', json_encode($template['CoEmailProvisionerTemplate']['message']),"Expected different variables");
  }


  public function testParseVariables() {
    $method = $this->getMethod($this->CEPT,"parseVariables");
    // test passing a template directly
    $templ3= $this->CEPTemplate->find('first',array(
        'conditions' => array('CoEmailProvisionerTemplate.id' => 3)));
    $vars=$method->invokeArgs($this->CEPT, array($templ3));
    $this->assertTextEquals('["VAR","name","template","NAME"]', json_encode($vars),"Expected different variables");
    $templ4= $this->CEPTemplate->find('first',array(
        'conditions' => array('CoEmailProvisionerTemplate.id' => 4)));
    $vars=$method->invokeArgs($this->CEPT, array($templ4));
    $this->assertTextEquals('["ADDRESS","NAME","GROUP","template"]', json_encode($vars),"Expected different variables");
  }

  public function testParseVariablesInText() {
    $method = $this->getMethod($this->CEPT,"parseVariablesInText");
    $vars=$method->invokeArgs($this->CEPT, array("test {NAME} variable"));
    $this->assertTextEquals('["NAME"]', json_encode($vars),"Variable NAME not found");

    $vars=$method->invokeArgs($this->CEPT, array("test without variables"));
    $this->assertTextEquals('[]',json_encode($vars), "Variables found where none expected");

    $vars=$method->invokeArgs($this->CEPT, array("{VAR} variable"));
    $this->assertTextEquals('["VAR"]',json_encode($vars), "Variable VAR not found");

    $vars=$method->invokeArgs($this->CEPT, array("test {VV}"));
    $this->assertTextEquals('["VV"]', json_encode($vars),"Variable VV not found");

    $vars=$method->invokeArgs($this->CEPT, array("test {VV} {VVB} {VVC} {VVD}"));
    $this->assertTextEquals('["VV","VVB","VVC","VVD"]',json_encode($vars), "Variables not found");

    // test only a-zA-Z0-9 for variable names
    $vars=$method->invokeArgs($this->CEPT, array("test {{NAME} {NAME2}} {{NAME3}} {JOKE-VAR} {0NOTFOUND} variable"));
    $this->assertTextEquals('["NAME","NAME2","NAME3"]',json_encode($vars), "Variables do not match a-zA-Z0-9 pattern");
    $vars=$method->invokeArgs($this->CEPT, array("test {name} {NaMe} {NAme} {n9mE} {0name} variable"));
    $this->assertTextEquals('["name","NaMe","NAme","n9mE"]',json_encode($vars), "Variables do not match A-Z pattern");
  }

  public function testSaveAll() {
    $adminaddress="ict@example.com";
    $subject1 = "Test subject";
    $message1="Test message";
    $subject2 = "Test subject 2";
    $message2="Test message 2";
    $this->CEPT->saveAll(array(
      "CoEmailProvisionerTarget" => array(
        "co_provisioning_target_id" => 1,
        "id" => 1,
        "adminaddress" => $adminaddress,
        "message_N_id" => 1,
        "message_N" => $message1,
        "message_subject_N" => $subject1,
        "message_E_id" => -1,
        "message_E" => $message2,
        "message_subject_E" => $subject2
       )
     ));

    // Test that the target with id 1 now has 7 templates. Template id 2 was not removed, because saveAll() expects all
    // current templates to be passed
    $this->CEPT->contain('CoEmailProvisionerTemplate');
    $target = $this->CEPT->find('first',array(
        'conditions' => array('CoEmailProvisionerTarget.id' => 1)));
    $this->assertTextEquals($adminaddress,$target['CoEmailProvisionerTarget']['adminaddress'], "Admin address was not saved");
    $this->assertEqual(7,sizeof($target['CoEmailProvisionerTemplate']),"expected 7 templates");

    foreach($target['CoEmailProvisionerTemplate'] as $template)
    {
      switch($template['id'])
      {
      case 2:
        $this->assertTextEquals("Subject for New Group",$template['subject'], "Template 2 subject was overwritten");
        $this->assertTextEquals("Template for New Group", $template['message'],"Template 2 message was overwritten");
        $this->assertTextEquals(EmailTemplateEnum::NewGroup,$template['template_type'],"Template 2 type was overwritten");
        $this->assertTextEquals('1999-12-11 11:23:45',$template['created'],"Template 2 creation time was overwritten");
        $this->assertTextEquals('1999-12-11 11:23:45',$template['modified'],"Template 2 modification time was overwritten");
        break;
      case 1:
        $this->assertTextEquals($subject1, $template['subject'], "Template 1 subject was not overwritten");
        $this->assertTextEquals($message1, $template['message'], "Template 1 message was not overwritten");
        $this->assertTextEquals(EmailTemplateEnum::NewPerson,$template['template_type'],"Template 1 type was not saved");
        $this->assertTextEquals('1999-12-11 11:23:45',$template['created'],"Template 1 creation time was overwritten");
        $this->assertTextNotEquals('1999-12-11 11:23:45',$template['modified'],"Template 1 modification time was overwritten");
        break;
      default:
        if($template['template_type'] == EmailTemplateEnum::UpdatePerson) {
          $this->assertTextEquals($subject2,$template['subject'], "Template UpdatePerson subject was not saved");
          $this->assertTextEquals($message2, $template['message'],"Template UpdatePerson message was not saved");
          $this->assertTextEquals($template['created'],$template['modified'],"Template UpdatePerson creation and modification time not set correctly");
        }
        break;
      }
    }
  }
}

class TestEmail extends CakeEmail
{
  public $_debug_content="";

  public function send($content = null) {
    $this->_debug_content = parent::send($content);
    return $this->_debug_content;
  }

}

class TestModel extends CakeTestModel
{
	public $recursive = 0;
	public $useTable=false;

	public $actsAs = array(
		'Containable'
	);

  public $log;
  public function __constructor() {
    $this->log=array();
  }
  public function __call($name, $args) {
    $this->log[]=json_encode(array("call"=>$name,"args"=>$args));
  }
}
