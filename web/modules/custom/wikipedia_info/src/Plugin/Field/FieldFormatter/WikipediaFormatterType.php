<?php

namespace Drupal\wikipedia_info\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Urodoz\Truncate\TruncateService;

/**
 * Plugin implementation of the 'wikipedia_formatter_type' formatter.
 *
 * @FieldFormatter(
 *   id = "wikipedia_formatter_type",
 *   label = @Translation("Wikipedia formatter type"),
 *   field_types = {
 *     "wikipedia_field_type"
 *   }
 * )
 */
class WikipediaFormatterType extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'max_length' => 160,
      // Implement default settings.
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
      $form['max_length'] = [
        '#type' => 'number',
        '#title' => t('Maximum length of article excerpt in characters'),
        '#description' => t('If this field is empty, text will not be truncated.'),
        '#default_value' => $this->getSetting('max_length'),
        '#required' => FALSE,
        '#min' => 1,
      ]
    ] + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    // Implement settings summary.

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = ['#markup' => $this->viewValue($item)];
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    $max_length = $this->getSetting('max_length');
    $client = \Drupal::service('wikipedia_client.client');
    // Search Wikipedia for a page matching this title.
    $wiki_data = is_array($client->getResponse($item->value)) ? $client->getResponse($item->value) : [];

    // Returns markup that contains the extract with
    // a link back to the Wikipedia source document.
    $markup = $client->getMarkup($wiki_data);

    if (!empty($max_length)) {
      $truncateService = new TruncateService();
      $markup = $truncateService->truncate($markup, $max_length);
    }

    return $markup;
  }

}
