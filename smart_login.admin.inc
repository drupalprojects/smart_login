<?php

/**
 * @file
 * Smart Login admins.
 * Inspired by IN2PIRE (contact@inspire.vn)
 */

function smart_login_settings($form, &$form_state) {
  $form['admin'] = array(
    '#type' => 'fieldset',
    '#title' => t('Admin Settings'),
    '#collapsible' => FALSE,
  );

  $theme_options = array('default' => t('Default admin theme'));
  $url_prefix = url(NULL, array('absolute' => TRUE)) . (variable_get('clean_url', 0) ? '' : '?q=');

  foreach (list_themes() as $theme_id => $theme) {
    $theme_options[$theme_id] = $theme->info['name'];
  }

  $form['admin']['smart_login_admin_theme'] = array(
    '#type' => 'select',
    '#title' => t('Theme'),
    '#description' => t('Theme to be used when user logins to Admin'),
    '#options' => $theme_options,
    '#default_value' => variable_get('smart_login_admin_theme', 'default'),
  );

  $form['admin']['smart_login_admin_destination'] = array(
    '#type' => 'textfield',
    '#title' => t('Login destination'),
    '#default_value' => variable_get('smart_login_admin_destination', ''),
    '#size' => 40,
    '#description' => t('The destination after user logins if there is no defined destination in url.'),
    '#field_prefix' => $url_prefix,
  );

  $form['admin']['smart_login_admin_loggedin_redirect'] = array(
    '#type' => 'textfield',
    '#title' => t('Logged in redirect'),
    '#default_value' => variable_get('smart_login_admin_loggedin_redirect', ''),
    '#size' => 40,
    '#description' => t('This page is displayed when a logged-in user goes to admin/login.'),
    '#field_prefix' => $url_prefix,
  );

  $form['front'] = array(
    '#type' => 'fieldset',
    '#title' => t('Frontend Settings'),
    '#collapsible' => FALSE,
  );

  $form['front']['smart_login_front_destination'] = array(
    '#type' => 'textfield',
    '#title' => t('Login destination'),
    '#default_value' => variable_get('smart_login_front_destination', ''),
    '#size' => 40,
    '#description' => t('The destination after user logins if there is no defined destination in url.'),
    '#field_prefix' => $url_prefix,
  );

  return system_settings_form($form);
}