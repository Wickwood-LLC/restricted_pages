<?php

namespace Drupal\restricted_pages\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Builds up the routes of all restricted_pages.
 *
 * @see \Drupal\views\Plugin\views\display\PathPluginBase
 */
class RouteSubscriber extends RouteSubscriberBase {

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
      $collection->add($restricted_page->getRouteId(), $route);

      $edit_route = new Route(
        '/admin/content/restricted-page/' . $restricted_page->id(),
        [
          '_entity_form' => 'restricted_page.edit',
          '_title' => 'Edit Restricted Page',
          'restricted_page' => $restricted_page->id(),
        ],
        [
          '_permission' => 'administer restricted_page',
        ]
      );
      $edit_route->addOptions([
        'parameters' => [
          'restricted_page' => ['type' => 'entity:restricted_page'],
        ],
      ]);
      $collection->add($restricted_page->getRouteId('edit'), $edit_route);

      $duplicate_route = new Route(
        '/admin/content/restricted-page/' . $restricted_page->id() . '/duplicate',
        [
          '_entity_form' => 'restricted_page.duplicate',
          '_title' => 'Duplicate Restricted Page',
          'restricted_page' => $restricted_page->id(),
        ],
        [
          '_permission' => 'administer restricted_page',
        ]
      );
      $duplicate_route->addOptions([
        'parameters' => [
          'restricted_page' => ['type' => 'entity:restricted_page'],
        ],
      ]);
      $collection->add($restricted_page->getRouteId('duplicate'), $duplicate_route);

      $delete_route = new Route(
        '/admin/content/restricted-page/' . $restricted_page->id() . '/delete',
        [
          '_entity_form' => 'restricted_page.delete',
          '_title' => 'Delete Restricted Page',
          'restricted_page' => $restricted_page->id(),
        ],
        [
          '_permission' => 'administer restricted_page',
        ]
      );
      $delete_route->addOptions([
        'parameters' => [
          'restricted_page' => ['type' => 'entity:restricted_page'],
        ],
      ]);
      $collection->add($restricted_page->getRouteId('delete'), $delete_route);

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

      $collection->add($restricted_page->getRouteId('registration'), $registration_route);
    }
    return $collection;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('layout_builder.restricted_page.view')) {
      $restricted_pages = $this->restrictedPageStorage->loadByProperties(['status' => TRUE]);
      foreach ($restricted_pages  as $restricted_page) {
        /** @var \Drupal\restricted_pages\Entity\RestrictedPage $restricted_page */
        $layout_builder_route = clone $route;
        // $layout_builder_route->setPath($restricted_page->getPath());
        $layout_builder_route->setPath(str_replace('{restricted_page}', $restricted_page->id(), $layout_builder_route->getPath()));
        $layout_builder_route->setDefault('_title', $restricted_page->label());
        $layout_builder_route->setDefault('restricted_page', $restricted_page->id());
        $options = $layout_builder_route->getOptions();
        $options['parameters']['restricted_page']['type'] = 'entity:restricted_page';
        $layout_builder_route->setOptions($options);
        $collection->add($restricted_page->getRouteId('layout_builder'), $layout_builder_route);
      }
      // TODO: Should it be removed?
      // $collection->remove('layout_builder.restricted_page.view');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events = parent::getSubscribedEvents();
    // We want to alter route after the LayoutBuilderRoutes::alterRoutes()
    // which uses -110 priority.
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -111];
    return $events;
  } 
}
