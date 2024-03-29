<?php

use CRM_Omnimail_ExtensionUtil as E;

// This enables custom fields for emails
return [
  [
    'name' => 'cg_extend_objects:Email',
    'entity' => 'OptionValue',
    'cleanup' => 'always',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'cg_extend_objects',
        'label' => E::ts('Emails'),
        'value' => 'Email',
        'name' => 'civicrm_email',
        'is_reserved' => TRUE,
        'is_active' => TRUE,
        'grouping' => '',
      ],
      'match' => [
        'name',
        'option_group_id',
      ],
    ],
  ],
  [
    'name' => 'CustomGroup_email_settings',
    'entity' => 'CustomGroup',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'email_settings',
        'title' => E::ts('Email Settings'),
        'extends' => 'Email',
        'extends_entity_column_id' => NULL,
        'extends_entity_column_value' => NULL,
        'style' => 'Inline',
        'collapse_display' => FALSE,
        'help_pre' => '',
        'help_post' => '',
        'table_name' => 'civicrm_value_email',
        'weight' => 40,
        'is_active' => TRUE,
        'is_multiple' => FALSE,
        'min_multiple' => NULL,
        'max_multiple' => NULL,
        'collapse_adv_display' => TRUE,
        'created_date' => '2023-08-08 01:50:55',
        'is_reserved' => FALSE,
        'is_public' => TRUE,
        'icon' => '',
      ],
      'match' => [
        'name',
      ],
    ],
  ],
  [
    'name' => 'CustomGroup_email_settings_CustomField_Snooze_date',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'email_settings',
        'name' => 'snooze_date',
        'label' => E::ts('Snooze until'),
        'data_type' => 'Date',
        'html_type' => 'Select Date',
        'default_value' => NULL,
        'is_required' => FALSE,
        'is_searchable' => FALSE,
        'is_search_range' => FALSE,
        'help_pre' => NULL,
        'help_post' => NULL,
        'attributes' => NULL,
        'is_active' => TRUE,
        'is_view' => FALSE,
        'options_per_line' => NULL,
        'text_length' => 255,
        'start_date_years' => NULL,
        'end_date_years' => NULL,
        'date_format' => 'yy-mm-dd',
        'time_format' => NULL,
        'note_columns' => 60,
        'note_rows' => 4,
        'column_name' => 'snooze_date',
        'option_group_id' => NULL,
        'serialize' => 0,
        'filter' => NULL,
        'in_selector' => FALSE,
        'fk_entity' => NULL,
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
];
