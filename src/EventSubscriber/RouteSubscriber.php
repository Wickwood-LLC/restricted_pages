<?php

namespace Drupal\restricted_pages\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Builds up the routes of all restricted_pages.
 *
 * @see \Drupal\views\Plugin\views\display\PathPluginBase
 */
class RouteSubscriber {

  /**
   * The restricted_page storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $restrictedPageStorage;

  /**
   * Constructs a \Drupal\restricted_pages\EventSubscriber\RouteSubscriber instance.
   * 
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->restrictedPageStorage = $entity_type_manager->getStorage('restricted_page');
  }

  /**
   * Returns a set of route objects.
   *
   * @return \Symfony\Component\Routing\RouteCollection
   *   A route collection.
   */
  public function routes() {
    $collection = new RouteCollection();
    
    $restricted_pages = $this->restrictedPageStorage->loadByProperties(['status' => TRUE]);
    foreach ($restricted_pages  as $restricted_page) {
      /** @var \Drupal\restricted_pages\Entity\RestrictedPage $restricted_page */
      $route_name = 'restricted_pages.restricted_page.' . $restricted_page->id();
      $base_path = rtrim($restricted_page->getPath(), '/') . '/' ;
      $base_path = '/' . ltrim($base_path, '/');
      $route = new Route(
        $base_path . '{user}',
        [
          '_controller' => '\Drupal\restricted_pages\Controller\RestrictedPageController::restrictedPage',
          '_title_callback' => '\Drupal\restricted_pages\Controller\RestrictedPageController::restrictedPageTitle',
          'user' => 0,
          'restricted_page' => $restricted_page->id(),
        ]
      );
      $route->addRequirements(['_permission' => 'access content']);
      $route->addOptions([
        'parameters' => [
          'user' => ['converter' => 'restricted_pages.user_code'],
          'restricted_page' => ['type' => 'entity:restricted_page'],
        ],
      ]);
      $collection->add($route_name, $route);

      $registration_route = new Route(
        $base_path . 'registration',
        [
          '_controller' => '\Drupal\restricted_pages\Controller\RestrictedPageController::restrictedPageRegistration',
          '_title_callback' => '\Drupal\restricted_pages\Controller\RestrictedPageController::restrictedPageRegistrationTitle',
          'restricted_page' => $restricted_page->id(),
        ]
      );
      $registration_route->addRequirements(['_permission' => 'access content']);
      $registration_route->addOptions([
        'parameters' => [
          'restricted_page' => ['type' => 'entity:restricted_page'],
        ],
      ]);

      $collection->add('restricted_pages.restricted_page_registration.' . $restricted_page->id(), $registration_route);
    }
    return $collection;
  }
}
