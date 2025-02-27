<?php

namespace Drupal\restricted_pages\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\restricted_pages\Entity\RestrictedPage;
use Drupal\user\UserInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for building restricted_pages.
 */
class RestrictedPageController extends ControllerBase {

  /**
   * Build the block instance add form.
   */
  public function restrictedPage(Request $request, UserInterface $user, RestrictedPage $restricted_page) {

    if ($user->isAnonymous()) {
      $user_code = $request->cookies->get('user_code');
      if (!empty($user_code)) {
        $url = Url::fromRoute('restricted_pages.restricted_page.' . $restricted_page->id(), ['user' => $user_code, 'restricted_page' => $restricted_page->id()]);
      }
      else {
        $url = Url::fromRoute('restricted_pages.restricted_page_registration.' . $restricted_page->id(), ['restricted_page' => $restricted_page]);
      }
      return new RedirectResponse($url->toString());
    }

    $config = \Drupal::config('restricted_pages.settings');

    $user_code = $request->attributes->get('_raw_variables')->get('user');
    setcookie('user_code', $user_code, time() + $config->get('user_code_cookied_validity') * 24 * 60 * 60, '/');

    return [
      'restricted_page' => [
        '#type' => 'restricted_page',
        '#restricted_page' => $restricted_page,
        '#cache' => [
          'tags' => [$restricted_page->getEntityTypeId() . ':' . $restricted_page->id()],
        ],
      ],
    ];
  }

  /**
   * The _title_callback for the restricted_page
   *
   * @param \Drupal\restricted_pages\Entity\RestrictedPage $restricted_page
   *   The restricted_page.
   *
   * @return string
   *   The restricted_page title.
   */
  public function restrictedPageTitle(RestrictedPage $restricted_page) {
    return $restricted_page->label();
  }

  /**
   * The _title_callback for the restricted_page registration
   *
   * @param \Drupal\restricted_pages\Entity\RestrictedPage $restricted_page
   *   The restricted_page.
   *
   * @return string
   *   The restricted_page registration title.
   */
  public function restrictedPageRegistrationTitle(RestrictedPage $restricted_page) {
    return $this->t('Register the %restricted_page restricted_page', ['%restricted_page' => $restricted_page->label()]);
  }

  public function restrictedPageRegistration(RestrictedPage $restricted_page) {
    $user_storage = $this->entityTypeManager()->getStorage('user');
    /** @var \Drupal\Core\Password\DefaultPasswordGenerator */
    $password_generator = \Drupal::service('password_generator');

    /** @var \Drupal\user\UserInterface */
    $new_user = $user_storage->create([]);
    $new_user->setPassword($password_generator->generate(12));

    $form_state_additions = [
      'restricted_page_id' => $restricted_page->id(),
    ];

    $config = \Drupal::config('restricted_pages.settings');
    $form_mode = $config->get('registration_form_mode');
    if (empty($form_mode)) {
      $form_mode = 'default';
    }
    $user_register_form = $this->entityFormBuilder()->getForm($new_user, $form_mode, $form_state_additions);

    return [
      'form' => $user_register_form,
    ];
  }
}
