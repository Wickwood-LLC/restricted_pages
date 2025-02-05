<?php

namespace Drupal\restricted_pages;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

/**
 * Provides a listing of Restricted Page entities.
 */
class RestrictedPageListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['label'] = $this->t('Label');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\restricted_pages\Entity\RestrictedPage $entity */
    $row['id'] = $entity->id();
    $row['title'] = $entity->toLink(NULL, 'edit-form')->toString();
    $row['status'] = $entity->getStatus() ? $this->t('Enabled') : $this->t('Disabled');
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $operations = [
      'layout' => [
        'title' => new TranslatableMarkup('Manage Layout'),
        'weight' => 1,
        'url' => Url::fromRoute(
          'layout_builder.restricted_page.view',
          [ 'restricted_page' => $entity->id() ]
        ),
      ],
    ] + parent::getOperations($entity);

    return $operations;
  }

}