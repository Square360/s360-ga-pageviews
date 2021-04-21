<?php

namespace Drupal\s360_ga_pageviews\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Configure Partner Send SMS API settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 's360_ga_pageviews_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    $config_names = [];

    foreach (s360_ga_pageviews_get_domains() as $domain) {
      $config_names[] = 's360_ga_pageviews.' . $domain['id'] . '.settings';
    }

    return $config_names;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = [
      '#markup' => implode('', [
        $this->t('<p>The "Google Analytics Settings" allow the module to pull GA pageViews based on the view_id and filter expression provided. This data populates a table that can be used to create custom views.</p>'),
        (
          s360_ga_pageviews_site_using_drupal_domains()
            ? $this->t('<p>You can override any of the default settings with domain specific settings.</p>')
            : ''
        ),
      ]),
    ];

    foreach (s360_ga_pageviews_get_domains() as $domain) {
      $domain_id = $domain['id'];
      $domain_settings = \Drupal::config('s360_ga_pageviews.' . $domain_id . '.settings');
      $domain_field_required = $domain_id == 'default' ? TRUE : FALSE;

      $form[$domain_id] = [
        '#type' => 'details',
        '#title' => $domain['name'] . ' - Google Analytics Settings',
        '#open' => ($domain_id == 'default' || s360_ga_pageviews_site_using_drupal_domains()) ? TRUE : !empty(array_filter($domain_settings->getRawData())),
      ];

      $form[$domain_id][$domain_id . '_key_file'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Authorization Config File'),
        '#description' => $this->t('JSON formatted credentials file from your GA <a href="@ga_dev_console" target="_blank">developers console</a>.', [
          '@ga_dev_console' => 'https://console.cloud.google.com/projectselector2/apis/credentials',
        ]),
        '#upload_location' => 'private://' . S360_GA_PAGEVIEWS_GA_KEY_FILE_FOLDER . '/',
        '#upload_validators' => [
          'file_validate_extensions' => ['json'],
        ],
        '#default_value' => $domain_settings->get('key_file'),
        '#required' => $domain_field_required,
      ];

      $form[$domain_id][$domain_id . '_view_id'] = [
        '#type' => 'textfield',
        '#title' => $this->t('View ID'),
        '#description' => $this->t('The ID of view for the GA property.'),
        '#default_value' => $domain_settings->get('view_id'),
        '#required' => TRUE,
        '#access' => ($domain_id === 'default' && s360_ga_pageviews_site_using_drupal_domains()) ? FALSE : TRUE,
      ];

      $form[$domain_id][$domain_id . '_filter_expression'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Filter Expression for Exclusions'),
        '#description' => $this->t('<p>The filter expression limits the data that is returned from the GA report.</p><p>Format should be "ga:pagePath!@[PARTIAL_PAGE_URL_TO_IGNORE]; e.g. "ga:pagePath!@/about;"</p><p>See the full documentation <a href="@ga_filter" target="_blank">here</a>', [
          '@ga_filter' => 'https://developers.google.com/analytics/devguides/reporting/core/v3/reference#filters',
        ]),
        '#default_value' => $domain_settings->get('filter_expression'),
        '#required' => $domain_field_required,
      ];

      $form[$domain_id][$domain_id . '_look_back'] = [
        '#type' => 'select',
        '#title' => $this->t('Number of days to look back'),
        '#description' => $this->t('How many days should GA report look back to get page views.'),
        '#options' => [
          '30' => $this->t('30 Days'),
          '60' => $this->t('60 Days'),
          '90' => $this->t('90 Days'),
        ],
        '#empty_value' => '',
        '#empty_option' => t('- Select -'),
        '#default_value' => $domain_settings->get('look_back'),
        '#required' => $domain_field_required,
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach (s360_ga_pageviews_get_domains() as $domain) {
      $domain_id = $domain['id'];
      $domain_settings_name = 's360_ga_pageviews.' . $domain_id . '.settings';
      $domain_settings = \Drupal::config($domain_settings_name);

      // Get $form_state values.
      $fs_key_file = $form_state->getValue($domain_id . '_key_file');
      $fs_view_id = $form_state->getValue($domain_id . '_view_id');
      $fs_filter_expression = $form_state->getValue($domain_id . '_filter_expression');
      $fs_look_back = $form_state->getValue($domain_id . '_look_back');

      // Get $domain values.
      $domain_key_file = $domain_settings->get('key_file');
      $domain_view_id = $domain_settings->get('view_id');
      $domain_filter_expression = $domain_settings->get('filter_expression');
      $domain_look_back = $domain_settings->get('look_back');

      // The form_state and domain key_files are not equal.
      if ($fs_key_file !== $domain_key_file) {
        if ($domain_key_file) {
          // Get the file id from $domain_key_file.
          $fid = reset($domain_key_file);

          // Load the file and delete it.
          $file = File::load($fid);

          if ($file) {
            $file->delete();
          }
        }

        // We have a form_state key_file.
        if (count($fs_key_file)) {
          // Get the file id from the form_state key_file.
          $fid = reset($fs_key_file);

          // Load the file and save it permanently.
          $file = File::load($fid);
          $file->setPermanent();
          $file->save();

          $this->config($domain_settings_name)
            ->set('key_file', $fs_key_file)
            ->save();
        }
        else {
          $this->config($domain_settings_name)
            ->clear('key_file')
            ->save();
        }
      }

      $this->config($domain_settings_name)
        ->set('view_id', $fs_view_id)
        ->set('filter_expression', $fs_filter_expression)
        ->set('look_back', $fs_look_back)
        ->save();
    }

    parent::submitForm($form, $form_state);
  }

}
