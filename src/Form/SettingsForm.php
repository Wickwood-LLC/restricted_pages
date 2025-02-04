<?php

namespace Drupal\restricted_pages\Form;

use Drupal\Core\Config\Config;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a form that configures settings.
 */
class SettingsForm extends ConfigFormBase {


  /**
   * The 'restricted_pages.settings' config object.
   */
  protected Config $config;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    $instance = parent::create($container);
    $instance->config = $container->get('config.factory')->getEditable('restricted_pages.settings');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'restricted_pages_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'restricted_pages.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL): array {

    $form['user_code_cookied_validity'] = [
      '#type' => 'number',
      '#title' => $this->t('Validity of Uer Code Cookie'),
      '#field_suffix' => $this->t('days'),
      '#size' => 4,
      '#min' => 0,
      '#max' => 1825, // 5 years.
      '#default_value' => $this->config->get('user_code_cookied_validity') ?? 30,
      '#description' => $this->t('Validity of the user code cookied in number of days.'),
    ];
    $entity_display_repo = \Drupal::service('entity_display.repository');
    $form_modes = $entity_display_repo->getFormModeOptionsByBundle('user', 'user');
    $form['registration_form_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Registration Form Mode'),
      '#default_value' => $this->config->get('registration_form_mode'),
      '#options' => $form_modes,
      '#empty_option' => $this->t('- Select a form mode -'),
      '#description' => $this->t('Choose a form mode to use for the registration form.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $values = $form_state->getValues();
    $this->config
      ->set('user_code_cookied_validity', $values['user_code_cookied_validity'])
      ->set('registration_form_mode', $values['registration_form_mode'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
