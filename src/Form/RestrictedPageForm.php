<?php

namespace Drupal\restricted_pages\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for adding/editing restricted_page entities.
 */
class RestrictedPageForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function setEntity(EntityInterface $entity) {
    /** @var \Drupal\restricted_pages\Entity\RestrictedPage $entity */
    if ($this->operation == 'duplicate') {
      $entity = $entity->createDuplicate();
      $entity->setLabel($this->t('Clone of @label', ['@label' => $entity->label()]));
    }
    $this->entity = $entity;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    if (!$restricted_page = $form_state->get('restricted_page')) {
      $restricted_page = $this->entity;
      $form_state->set('restricted_page', $restricted_page);
    }

    /** @var \Drupal\restricted_pages\Entity\RestrictedPage $restricted_page */

    $form['#attributes']['id'] = 'restricted-page-' . $restricted_page->isNew() ? 'new' : $restricted_page->id();

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $restricted_page->getLabel(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('ID'),
      '#default_value' => $restricted_page->id(),
      '#machine_name' => [
        'exists' => '\Drupal\restricted_pages\Entity\RestrictedPage::load',
      ],
      '#disabled' => !$restricted_page->isNew(),
    ];

    $form['path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path'),
      '#default_value' => $restricted_page->getPath(),
      '#required' => TRUE,
      '#maxlength' => 255,
      '#description' => $this->t('Specify the path of this restricted page.'),
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $restricted_page->getStatus(),
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {

    $path = $form_state->getValue('path');
    $errors = [];
    if (strpos($path, '%') !== FALSE) {
      $form_state->setErrorByName('path', $this->t('"%" may not be used in the path.'));
    }

    $parsed_url = UrlHelper::parse($path);
    if (empty($parsed_url['path'])) {
      $form_state->setErrorByName('path', $this->t('Path is empty.'));
    }

    if (!empty($parsed_url['query'])) {
      $form_state->setErrorByName('path', $this->t('No query allowed.'));
    }

    if (!parse_url('internal:/' . $path)) {
      $form_state->setErrorByName('path', $this->t('Invalid path. Valid characters are alphanumerics as well as "-", ".", "_" and "~".'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = $entity->save();

    if ($status === SAVED_NEW) {
      $this->messenger()->addMessage($this->t('Created the %label restricted page.', [
        '%label' => $entity->label(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('Updated the %label restricted page.', [
        '%label' => $entity->label(),
      ]));
    }

    $form_state->setRedirectUrl($entity->toUrl('collection'));
  }

}