<?php

/**
 * @file
 * Smart Login forms.
 * Inspired by IN2PIRE (contact@inspire.vn)
 */

function _smart_login_form_alter(&$form, &$form_state) {
  form_load_include($form_state, 'inc', 'smart_login', 'smart_login.forms');
  $admin_theme = smart_login_get_admin_theme();
  $css_file = drupal_get_path('module', 'smart_login') . '/css/' . $admin_theme . '.css';

  if (file_exists($css_file)) {
    $form['#attached']['css'][$css_file] = array(
      'type' => 'file',
      'group' => CSS_THEME,
      'weight' => 1000,
    );
  }
}

/**
 * @see user_login_block().
 * @see smart_login_user_login_submit().
 */
function smart_login_form_user_login_block_alter(&$form, &$form_state) {
  $form['#submit'][] = 'smart_login_user_login_submit';
}

/**
 * @see user_login().
 * @see smart_login_user_login_submit().
 */
function smart_login_form_user_login_alter(&$form, &$form_state) {
  $form['#submit'][] = 'smart_login_user_login_submit';

  if (!path_is_admin(current_path())) {
    return;
  }

  _smart_login_form_alter($form, $form_state);

  $form['actions']['link'] = array(
    '#type' => 'link',
    '#title' => t('Forgot password?'),
    '#href' => 'admin/password',
  );
}

/**
 * @see smart_login_form_user_login_alter().
 */
function smart_login_user_login_submit($form, &$form_state) {
  $form_state['redirect'] = smart_login_destination();
}

/**
 * @see user_pass().
 * @see smart_login_user_pass_submit().
 */
function smart_login_form_user_pass_alter(&$form, &$form_state) {
  if (!path_is_admin(current_path())) {
    return;
  }

  _smart_login_form_alter($form, $form_state);

  $form['actions']['link'] = array(
    '#type' => 'link',
    '#title' => t('Login'),
    '#href' => 'admin/login',
  );

  $form['#submit'][] = 'smart_login_user_pass_submit';
}

/**
 * @see smart_login_form_user_pass_alter();
 * @see smart_login_form_user_login_block_alter();
 */
function smart_login_user_pass_submit(&$form, &$form_state) {
  $form_state['redirect'] = 'admin/login';
}

function smart_login_form_system_site_information_settings_alter(&$form, &$form_state) {
  $form['error_page']['site_403']['#weight'] = 1;
  $form['error_page']['site_404']['#weight'] = 2;

  $form['error_page']['smart_login_site_403'] = $form['error_page']['site_403'];

  $site_403 = &$form['error_page']['smart_login_site_403'];
  $site_403['#default_value'] = variable_get('smart_login_site_403', '');

  unset($site_403);
  unset($form['error_page']['site_403']);
}
