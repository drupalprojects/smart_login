<?php

/**
 * @file
 * Definition of \Drupal\smart_login\SmartLoginController.
 */

namespace Drupal\smart_login;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for Smart Login.
 */
class SmartLoginController extends ControllerBase {
  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The HTTP kernel to handle forms returning response objects.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernel
   */
  protected $httpKernel;

  /**
   * Creates a new HelpController.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(RouteMatchInterface $route_match, FormBuilderInterface $form_builder, AliasManagerInterface $alias_manager = NULL, HttpKernel $http_kernel = NULL) {
    $this->routeMatch = $route_match;
    $this->formBuilder = $form_builder;
    $this->aliasManager = $alias_manager;
    $this->httpKernel = $http_kernel;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('form_builder'),
      $container->get('path.alias_manager'),
      $container->get('http_kernel')
    );
  }

  /**
   * Error 403 Page.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A Symfony direct response object.
   */
  public function error403Page() {
    $request = \Drupal::request();
    $destination = $request->get('destination', NULL);
    $request->query->set('destination', NULL);

    if (\Drupal::currentUser()->isAuthenticated()) {
      // Access Restricted.
      $page403 = \Drupal::config('smart_login.settings')->get('page.403');
      $path = $this->aliasManager->getPathByAlias($page403);
      $return = NULL;

      if ($path && $path != current_path() && $path != $destination) {
        if ($request->getMethod() === 'POST') {
          $subrequest = Request::create($request->getBaseUrl() . '/' . $path, 'POST', ['destination' => $destination, '_exception_statuscode' => 403] + $request->request->all(), $request->cookies->all(), [], $request->server->all());
        }
        else {
          $subrequest = Request::create($request->getBaseUrl() . '/' . $path, 'GET', ['destination' => $destination, '_exception_statuscode' => 403], $request->cookies->all(), [], $request->server->all());
        }

        $response = $this->httpKernel->handle($subrequest, HttpKernelInterface::SUB_REQUEST);
        return $response;
      }

      $content = [
        '#markup' => $this->t('You are not authorized to access this page.'),
        '#title' => $this->t('Access denied'),
      ];

      return $content;
    }

    // Redirect to login page.
    if (path_is_admin($destination)) {
      $url = 'admin/login';
    }
    else {
      $url = 'user/login';
    }

    $url = url($url, ['query' => ['destination' => $destination], 'absolute' => TRUE]);

    return new RedirectResponse($url, 302);
  }

  /**
   * Login Page.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A Symfony direct response object.
   */
  public function loginPage() {
    if (\Drupal::currentUser()->isAuthenticated()) {
      $url = \Drupal::config('smart_login.settings')->get('admin.loggedin_redirect');

      if (empty($url)) {
        $url = 'admin';
      }

      $url = url($url, ['absolute' => TRUE]);
      $status = 302;

      return new RedirectResponse($url, $status);
    }

    $build = [];
    $build['#title'] = $this->t('Log in to @site', ['@site' => \Drupal::config('system.site')->get('name')]);
    $build[] = \Drupal::formBuilder()->getForm('\Drupal\user\Form\UserLoginForm');

    return $build;
  }

  /**
   * Forgot Passowrd Page.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A Symfony direct response object.
   */
  public function passwordPage() {
    $build = [];
    $build[] = \Drupal::formBuilder()->getForm('\Drupal\user\Form\UserPasswordForm');

    return $build;
  }
}
