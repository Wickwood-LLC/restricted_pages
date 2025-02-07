<?php

namespace Drupal\restricted_pages\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;

/**
 * Load the currently logged in user into the token environment.
 *
 * @Action(
 *   id = "restricted_pages_get_user_code_user",
 *   label = @Translation("Get User of User Code cookie"),
 *   description = @Translation("Load the user of the user code saved in the cookie."),
 * )
 */
class GetUserCodeUserAction extends ConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return ['token_name' => 'user'] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['token_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name of token'),
      '#description' => $this->t('The loaded user will be stored into this token.'),
      '#default_value' => $this->configuration['token_name'],
      '#eca_token_reference' => TRUE,
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['token_name'] = $form_state->getValue('token_name');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function execute(): void {
    $request = \Drupal::request();

    $user_code = $request->cookies->get('user_code');
    if (!empty($user_code)) {
      /** @var \Drupal\restricted_pages\ParamConverter\UserCodeToEntityConverter */
      $user_code_service = \Drupal::service('restricted_pages.user_code');
      $user_id = $user_code_service->getUserID($user_code);
      if ($user = $this->entityTypeManager->getStorage('user')->load($user_id)) {
        $token_name = trim($this->configuration['token_name'] ?? '');
        if ($token_name === '') {
          $token_name = 'user';
        }
        $this->tokenService->addTokenData($token_name, $user);
      }
    }
  }

}
