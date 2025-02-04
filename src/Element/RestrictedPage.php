<?php

namespace Drupal\restricted_pages\Element;

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
    return $element;
  }

}
