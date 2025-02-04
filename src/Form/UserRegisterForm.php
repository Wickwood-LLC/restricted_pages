<?php

namespace Drupal\restricted_pages\Form;

use Drupal\user\RegisterForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Custom form class for the "custom_register" form mode.
 */
class UserRegisterForm extends RegisterForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Build the form using the parent class.
    $form = parent::buildForm($form, $form_state);

    // restricted_page registration does not require password and username to set.
    $form['account']['pass']['#access'] = FALSE;
    $form['account']['pass']['#required'] = FALSE;
    $form['account']['name']['#access'] = FALSE;
    $form['account']['status']['#access'] = FALSE;
    $form['account']['roles']['#access'] = FALSE;
    $form['account']['notify']['#access'] = FALSE;

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('mail');

    $form_state->setValue('name', $email);

    $user_storage = \Drupal::entityTypeManager()->getStorage('user');
    $user_ids = $user_storage->getQuery()->accessCheck(FALSE)
      ->condition('mail', $email)
      ->execute();

    parent::validateForm($form, $form_state);

    if (!empty($user_ids)) {
      $form_state->set('existing_user_id', reset($user_ids));
      $form_state->clearErrors();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $existing_user_id = $form_state->get('existing_user_id');
    if ($existing_user_id) {
      $this->setRestrictedPageRedirection($existing_user_id, $form_state);
      \Drupal::messenger()->addMessage('You are already registered!');
    }
    else {
      // Perform custom submission logic for this form mode.
      \Drupal::messenger()->addMessage('Custom registration form submitted!');

      // Call the parent submission logic.
      parent::submitForm($form, $form_state);
    }
  }

  public function save(array $form, FormStateInterface $form_state) {
    $existing_user_id = $form_state->get('existing_user_id');
    if (!$existing_user_id) {
      parent::save($form, $form_state);
      $account = $form_state->get('user');
      $this->setRestrictedPageRedirection($account->id(), $form_state);
    }
  }

  protected function setRestrictedPageRedirection($user_id, FormStateInterface $form_state) {
    $config = \Drupal::config('restricted_pages.settings');
    $restricted_page_id = $form_state->get('restricted_page_id');
    /** @var \Drupal\restricted_pages\ParamConverter\UserCodeToEntityConverter */
    $user_code_service = \Drupal::service('restricted_pages.user_code');
    $user_code = $user_code_service->getUserCode($user_id);
    setcookie('user_code', $user_code, time() + $config->get('user_code_cookied_validity') * 24 * 60 * 60, '/');
    $restricted_page_redirect = Url::fromRoute('restricted_pages.restricted_page.' . $restricted_page_id, ['user' => $user_code, 'restricted_page' => $restricted_page_id])
      ->toString();
    $form_state->set('restricted_page_redirect', $restricted_page_redirect);
    $form_state->setResponse(new RedirectResponse($restricted_page_redirect));
  }
}
