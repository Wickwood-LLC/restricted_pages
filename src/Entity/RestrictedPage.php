<?php

namespace Drupal\restricted_pages\Entity;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\Context\EntityContext;
use Drupal\layout_builder\SectionListInterface;
use Drupal\layout_builder\SectionListTrait;

/**
 * Defines the RestrictedPage configuration entity.
 *
 * @ConfigEntityType(
 *   id = "restricted_page",
 *   label = @Translation("Restricted Page"),
 *   label_collection = @Translation("Restricted Page"),
 *   label_singular = @Translation("Restricted Page"),
 *   label_plural = @Translation("Restricted Pages"),
 *   label_count = @PluralTranslation(
 *     singular = "@count restricted page",
 *     plural = "@count restricted pages",
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\restricted_pages\Entity\RestrictedPageStorage",
 *     "list_builder" = "Drupal\restricted_pages\RestrictedPageListBuilder",
 *     "form" = {
 *       "add" = "Drupal\restricted_pages\Form\RestrictedPageForm",
 *       "edit" = "Drupal\restricted_pages\Form\RestrictedPageForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *       "duplicate" = "Drupal\restricted_pages\Form\RestrictedPageForm",
 *       "layout_builder" = "Drupal\restricted_pages\Form\RestrictedPageLayoutBuilderForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "restricted_page",
 *   admin_permission = "administer restricted_page",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "status" = "status",
 *   },
 *   links = {
 *     "add-form" = "/admin/content/restricted-page/add",
 *     "edit-form" = "/admin/content/restricted-page/{restricted_page}",
 *     "delete-form" = "/admin/content/restricted-page/{restricted_page}/delete",
 *     "duplicate-form" = "/admin/content/restricted-page/{restricted_page}/duplicate",
 *     "collection" = "/admin/content/restricted-page",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "status",
 *     "path",
 *     "sections",
 *   },
 *   cache = {
 *     "tags" = {"restricted_page_list", "restricted_page:{id}"}
 *   }
 * )
 */
class RestrictedPage extends ConfigEntityBase implements SectionListInterface {
  use SectionListTrait;

  /**
   * The ID of the restricted_page.
   *
   * @var string
   */
  protected $id;

  /**
   * The label of the restricted_page.
   *
   * @var string
   */
  protected $label;

  /**
   * The status of the restricted_page.
   *
   * @var bool
   */
  protected $status = TRUE;

  /**
   * The path of the restricted_page.
   */
  protected $path;

  public $sections = [];


  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatus($status) {
    $this->status = $status;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPath() {
    return $this->path;
  }

  /**
   * Gets the layout sections.
   *
   * @return \Drupal\layout_builder\Section[]
   *   A sequentially and numerically keyed array of section objects.
   */
  public function getSections() {
    return $this->sections;
  }

  /**
   * Stores the information for all sections.
   *
   * Implementations of this method are expected to call array_values() to rekey
   * the list of sections.
   *
   * @param \Drupal\layout_builder\Section[] $sections
   *   An array of section objects.
   *
   * @return $this
   */
  protected function setSections(array $sections) {
    $this->sections = array_values($sections);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Rebuild the router if this is a new page, or its status changed or its path changed.
    if (
      !isset($this->original) ||
      ($this->status() != $this->original->status()) ||
      ($this->getPath() != $this->original->getPath())
    ) {
      \Drupal::service('router.builder')->setRebuildNeeded();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {

    /** @var \Drupal\layout_builder\LayoutTempstoreRepositoryInterface */
    $layoutTempstoreRepository = \Drupal::service('layout_builder.tempstore_repository');
    // Delete all temporary layout builder section storage for this entity.
    // Or it cause issues when a another entity creaerd with same name or when re-installed this module.
    foreach ($entities as $entity) {
      $contextRepository = \Drupal::service('context.repository');
      $contexts = $contextRepository->getAvailableContexts();
      $contexts['display'] = EntityContext::fromEntity($entity);
      $contexts['layout_builder.entity'] = EntityContext::fromEntity($entity);

      $sectionStorageManager = \Drupal::service('plugin.manager.layout_builder.section_storage');

      // Get section storage to pass to contexts hook.
      $cacheability = new CacheableMetadata();
      $section_storage = $sectionStorageManager->findByContext($contexts, $cacheability);
      $layoutTempstoreRepository->delete($section_storage);
    }
    return parent::preDelete($storage, $entities);
  }
}
