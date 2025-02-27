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
      $id = 'restricted_pages.restricted_page.' . $restricted_page->id();
      $this->derivatives[$id] = [
        'route_name' => $id,
        'title' => $this->t('View'),
        'base_route' => 'entity.restricted_page.edit_form',
        'weight' => -100,
      ];
    }
    return $this->derivatives;
  }

}
