<?php
/* Add the undescore case of the controller, no need to specify crud operations*/
$apiResources = [
  'modules',
  'business_type',
  'company',
  'company_logo',
  'company_branch',
  'company_branch_employee',
  'position',
  'api_test_results',
  'account',
  'account_type',
  'account_information',
  'account_profile_picture',
  'queue_form',
  'queue_form_field',
  'queue_card',
  'queue_card_field',
  'announcement',
];
api_resource($apiResources);

?>