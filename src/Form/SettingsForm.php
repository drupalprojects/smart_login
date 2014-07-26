<?php

/**
 * @file
 * Contains \Drupal\smart_login\Form\SettingsForm.
 */

namespace Drupal\smart_login\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure settings for Smart Login.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Constructs a SiteInformationForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The path alias manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AliasManagerInterface $alias_manager) {
    parent::__construct($config_factory);

    $this->aliasManager = $alias_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('path.alias_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'smart_login_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $moduleConfig = $this->config('smart_login.settings');
    $urlPrefix = url(NULL, ['absolute' => TRUE]);

    $theme_options = ['default' => t('Default admin theme')];

    foreach (list_themes() as $theme_id => $theme) {
      $theme_options[$theme_id] = $theme->info['name'];
    }

    $form['admin'] = [
      '#type' => 'fieldset',
      '#title' => t('Admin Settings'),
      '#collapsible' => FALSE,
    ];

    $form['admin']['admin_theme'] = [
      '#type' => 'select',
      '#title' => t('Theme'),
      '#description' => t('The theme to be used when user goes to admin/login.'),
      '#options' => $theme_options,
      '#default_value' => $moduleConfig->get('admin.theme'),
    ];

    $form['admin']['admin_destination'] = [
      '#type' => 'textfield',
      '#title' => t('Login destination'),
      '#default_value' => $moduleConfig->get('admin.destination'),
      '#size' => 40,
      '#description' => t('The destination after user logins if there is no defined destination in url.'),
      '#field_prefix' => $urlPrefix,
    ];

    $form['admin']['admin_loggedin_redirect'] = [
      '#type' => 'textfield',
      '#title' => t('Logged in redirect'),
      '#default_value' => $moduleConfig->get('admin.loggedin_redirect'),
      '#size' => 40,
      '#description' => t('This page is displayed when a logged-in user goes to admin/login.'),
      '#field_prefix' => $urlPrefix,
    ];

    $form['front'] = [
      '#type' => 'fieldset',
      '#title' => t('Frontend Settings'),
      '#collapsible' => FALSE,
    ];

    $form['front']['front_destination'] = [
      '#type' => 'textfield',
      '#title' => t('Login destination'),
      '#default_value' => $moduleConfig->get('front.destination'),
      '#size' => 40,
      '#description' => t('The destination after user logins if there is no defined destination in url.'),
      '#field_prefix' => $urlPrefix,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array &$form_state) {
    $values = &$form_state['values'];

    if (!empty($values['admin_destination'])) {
      form_set_value($form['admin']['admin_destination'], $this->aliasManager->getPathByAlias($values['admin_destination']), $form_state);

      if (!drupal_valid_path($values['admin_destination'])) {
        $this->setFormError('admin_destination', $form_state, $this->t("The path '%path' is either invalid or you do not have access to it.", ['%path' => $values['admin_destination']]));
      }
    }


    if (!empty($values['admin_loggedin_redirect'])) {
      form_set_value($form['admin']['admin_loggedin_redirect'], $this->aliasManager->getPathByAlias($values['admin_loggedin_redirect']), $form_state);

      if (!drupal_valid_path($values['admin_loggedin_redirect'])) {
        $this->setFormError('admin_loggedin_redirect', $form_state, $this->t("The path '%path' is either invalid or you do not have access to it.", ['%path' => $values['admin_loggedin_redirect']]));
      }
    }

    if (!empty($values['front_destination'])) {
      form_set_value($form['front']['front_destination'], $this->aliasManager->getPathByAlias($values['front_destination']), $form_state);

      if (!drupal_valid_path($values['front_destination'])) {
        $this->setFormError('front_destination', $form_state, $this->t("The path '%path' is either invalid or you do not have access to it.", ['%path' => $values['front_destination']]));
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $values = &$form_state['values'];

    $this->config('smart_login.settings')
      ->set('admin.theme', $values['admin_theme'])
      ->set('admin.destination', $values['admin_destination'])
      ->set('admin.loggedin_redirect', $values['admin_loggedin_redirect'])
      ->set('front.destination', $values['front_destination'])
      ->save();

    parent::submitForm($form, $form_state);
  }
}
