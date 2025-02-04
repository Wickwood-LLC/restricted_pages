<?php

namespace Drupal\restricted_pages\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

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
 *     "list_builder" = "Drupal\restricted_pages\RestrictedPageListBuilder",
 *     "form" = {
 *       "add" = "Drupal\restricted_pages\Form\RestrictedPageForm",
 *       "edit" = "Drupal\restricted_pages\Form\RestrictedPageForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *       "duplicate" = "Drupal\restricted_pages\Form\RestrictedPageForm"
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
 *     "add-form" = "/admin/structure/restricted-page/add",
 *     "edit-form" = "/admin/structure/restricted-page/{restricted_page}/edit",
 *     "delete-form" = "/admin/structure/restricted-page/{restricted_page}/delete",
 *     "duplicate-form" = "/admin/structure/restricted-page/{restricted_page}/duplicate",
 *     "collection" = "/admin/structure/restricted-page",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "status",
 *     "path",
 *   },
 *   cache = {
 *     "tags" = {"restricted_page_list", "restricted_page:{id}"}
 *   }
 * )
 */
class RestrictedPage extends ConfigEntityBase {

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
}
