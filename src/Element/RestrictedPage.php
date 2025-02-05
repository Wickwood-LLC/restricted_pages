<?php

namespace Drupal\restricted_pages\Element;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\Context\EntityContext;
use Drupal\Core\Render\Attribute\RenderElement;
use Drupal\Core\Render\Element\RenderElementBase;

/**
 * Provides a render element for a restricted_page.
 */
#[RenderElement('restricted_page')]
class RestrictedPage extends RenderElementBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = static::class;
    return [
      '#pre_render' => [
        [$class, 'preRender'],
      ],
      '#options' => [],
      '#attributes' => [],
      '#theme' => 'restricted_page',
    ];
  }

  public static function preRender($element) {
    if (!isset($element['#attributes']['class'])) {
      $element['#attributes']['class'] = [];
    }
    $element['#attributes']['class'][] = 'restricted-page';

    $contextRepository = \Drupal::service('context.repository');
    $contexts = $contextRepository->getAvailableContexts();
    $contexts['display'] = EntityContext::fromEntity($element['#restricted_page']);
    $contexts['layout_builder.entity'] = EntityContext::fromEntity($element['#restricted_page']);

    $sectionStorageManager = \Drupal::service('plugin.manager.layout_builder.section_storage');

    // Get section storage to pass to contexts hook.
    $cacheability = new CacheableMetadata();
    $storage = $sectionStorageManager->findByContext($contexts, $cacheability);

    $element['#content'] = [];
    foreach ($storage->getSections() as $delta => $section) {
      $element['#content'][$delta] = $section->toRenderArray($contexts);
    }

    // The render array is built based on decisions made by @SectionStorage
    // plugins and therefore it needs to depend on the accumulated
    // cacheability of those decisions.
    $cacheability->applyTo($element['#content']);

    return $element;
  }

}
