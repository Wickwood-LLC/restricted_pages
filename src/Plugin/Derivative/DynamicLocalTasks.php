<?php

namespace Drupal\restricted_pages\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generates media-related local tasks.
 */
class DynamicLocalTasks extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The restricted_page storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $restrictedPageStorage;

  /**
   * Creates a DynamicLocalTasks object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->restrictedPageStorage = $entity_type_manager->getStorage('restricted_page');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $restricted_pages = $this->restrictedPageStorage->loadByProperties(['status' => TRUE]);
    foreach ($restricted_pages as $restricted_page) {
      /** @var \Drupal\restricted_pages\Entity\RestrictedPage $restricted_page */
      $view_route = $restricted_page->getRouteId();
      $this->derivatives[$view_route] = [
        'route_name' => $view_route,
        'title' => $this->t('View'),
        'base_route' => $view_route,
        'weight' => 1,
      ];

      $this->derivatives[$restricted_page->getRouteId('edit')] = [
        'route_name' => $restricted_page->getRouteId('edit'),
        'title' => $this->t('Edit'),
        'base_route' => $view_route,
        'weight' => 3,
      ];

      $this->derivatives[$restricted_page->getRouteId('layout_builder')] = [
        'route_name' => $restricted_page->getRouteId('layout_builder'),
        'title' => $this->t('Layout'),
        'base_route' => $view_route,
        'weight' => 5,
      ];

      $this->derivatives[$restricted_page->getRouteId('duplicate')] = [
        'route_name' => $restricted_page->getRouteId('duplicate'),
        'title' => $this->t('Duplicate'),
        'base_route' => $view_route,
        'weight' => 7,
      ];

      $this->derivatives[$restricted_page->getRouteId('delete')] = [
        'route_name' => $restricted_page->getRouteId('delete'),
        'title' => $this->t('Delete'),
        'base_route' => $view_route,
        'weight' => 9,
      ];
    }
    return $this->derivatives;
  }

}
