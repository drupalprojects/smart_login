<?php

/**
 * @file
 * Smart Login pages.
 * Inspired by IN2PIRE (contact@inspire.vn).
 */

/**
 * Error 403 page.
 */
function smart_login_error_403_page() {
  if (!user_is_logged_in()) {
    $path = $_GET['destination'];
    $destination = array('destination' => $_GET['destination']);
    unset($_GET['destination']);

    if (path_is_admin($path)) {
      drupal_goto('admin/login', array('query' => $destination));
    }
    else {
      drupal_goto('user/login', array('query' => $destination));
    }
  }

  return;
}

/**
 * Admin login page.
 */
function smart_login_admin_login_page() {
  if (user_is_logged_in()) {
    $url = variable_get('smart_login_admin_loggedin_redirect', 'admin');

    if (empty($url)) {
      $url = 'admin';
    }

    drupal_goto($url);
  }

  drupal_set_title(t('Log in to @site', array('@site' => variable_get('site_name', 'Dashboard'))));

  return drupal_get_form('user_login');
}

/**
 * Admin password page.
 */
function smart_login_admin_password_page() {
  module_load_include('inc', 'user', 'user.pages');

  $form_state = array();
  $form_state['build_info']['args'] = array();
  $form = drupal_build_form('user_pass', $form_state);
  form_load_include($form_state, 'inc', 'user', 'user.pages');

  return $form;
}
