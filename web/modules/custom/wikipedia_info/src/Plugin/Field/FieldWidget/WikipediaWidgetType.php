<?php

namespace Drupal\wikipedia_info\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'wikipedia_widget_type' widget.
 *
 * @FieldWidget(
 *   id = "wikipedia_widget_type",
 *   label = @Translation("Wikipedia"),
 *   field_types = {
 *     "wikipedia_field_type"
 *   }
 * )
 */
class WikipediaWidgetType extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'size' => 60,
      'placeholder' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['size'] = [
      '#type' => 'number',
      '#title' => t('Size of textfield'),
      '#default_value' => $this->getSetting('size'),
      '#required' => TRUE,
      '#min' => 1,
    ];
    $elements['placeholder'] = [
      '#type' => 'textfield',
      '#title' => t('Placeholder'),
      '#default_value' => $this->getSetting('placeholder'),
      '#description' => t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = t('Textfield size: @size', ['@size' => $this->getSetting('size')]);
    if (!empty($this->getSetting('placeholder'))) {
      $summary[] = t('Placeholder: @placeholder', ['@placeholder' => $this->getSetting('placeholder')]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['value'] = [
      '#type' => 'textfield',
      '#title' => t('Wikipedia article title'),
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#size' => $this->getSetting('size'),
      '#placeholder' => $this->getSetting('placeholder'),
      '#maxlength' => $this->getFieldSetting('max_length'),
      '#element_validate' => [
        [$this, 'validate'],
      ],
    ];
    $element['lang_code'] = [
      '#type' => 'textfield',
      '#title' => t('Wikipedia lang code'),
      '#default_value' => isset($items[$delta]->lang_code) ? $items[$delta]->lang_code : 'en',
        '#size' => 10,
        '#placeholder' => $this->getSetting('placeholder'),
        '#maxlength' => $this->getFieldSetting('max_length'),
      ];

    return $element;
  }

  public function validate($element, FormStateInterface $form_state) {
    $wiki_data = [];
    $value = $element['#value'];
    if (strlen($value) == 0) {
      $form_state->setValueForElement($element, '');
      return;
    }

    $wikipedia_client = \Drupal::service('wikipedia_client.client');
    // Search Wikipedia for a page matching this title.
    $wiki_data = $wikipedia_client->getResponse($value);

    // If no match was found, the result will be empty.
    if (array_key_exists('missing',$wiki_data)) {
      $form_state->setError($element, t("The article with specified title couldn't be found. Try another value, please."));
    }
  }

}
