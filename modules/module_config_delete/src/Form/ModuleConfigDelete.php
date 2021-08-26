<?php

namespace Drupal\module_config_delete\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
// use Drupal\core\Config\FileStorage;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
// use Drupal\Core\Config\ConfigManager;
// use Drupal\Core\Config\DatabaseStorage;

/**
 * Defines a form for the module config delete module.
 */
class ModuleConfigDelete extends ConfigFormBase {

 /**
  * {@inheritdoc}
  */
  public function getFormId() {
    return 'settings';
  }

 /**
  * {@inheritdoc}
  */
  protected function getEditableConfigNames() {
    return [
      'module_config_delete.settings',
    ];
  }

 /**
  * {@inheritdoc}
  */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('module_config_delete.settings');

    $form['#tree'] = TRUE;
    
    $form['mcd_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => 'Define Config Files Location',
    ];
    
    $form['mcd_wrapper']['module_name'] = [
      '#type' => 'textfield',
      '#title' => t('Module Name'),
      '#required' => true,
      '#description' => t('The module machine name.'),
    ];

    $form['mcd_wrapper']['directory_path'] = [
      '#type' => 'textfield',
      '#title' => t('Directory Path'),
      '#required' => true,
      '#description' => t('The relative path to files. e.g. /config/install'),
    ];

    $form['mcd_testing_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => 'Preview active config to be deleted',
      '#prefix' => '<div id="mcd-testing-wrapper">',
      '#suffix' => '</div>',
    ];

    $form['mcd_testing_wrapper']['spacer_1'] = [
      '#type' => 'item',
      '#markup' => $this->t('<br />'),
    ];

    $form['mcd_testing_wrapper']['test_remove_config'] = [
      '#type' => 'submit',
      '#value' => $this->t('Test Config Removal'),
      '#submit' => [static::class, 'testRemoveModuleConfig'],
      '#ajax' => [
        'callback' => '::testRemovalCallback',
        'wrapper' => 'mcd-testing-wrapper',
      ],
    ];

    $form['mcd_testing_wrapper']['spacer_2'] = [
      '#type' => 'item',
      '#markup' => $this->t('<br />'),
    ];

    $form['mcd_testing_wrapper']['config_files_list'] = [
      '#type' => 'item',
      '#description' => 'Any active config found will be listed here.',
    ];

    $form['mcd_config_removal_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => 'Delete Active Module Config',
      '#description' => t('This action cannot be undone.'),
    ];

    $form['mcd_config_removal_wrapper']['spacer_1'] = [
      '#type' => 'item',
      '#markup' => $this->t('<br />'),
    ];

    $form['mcd_config_removal_wrapper']['remove_config'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete Active Module Config'),
      '#submit' => ['::removeModuleConfig'],
    ];

    $form['mcd_config_removal_wrapper']['spacer_2'] = [
      '#type' => 'item',
      '#markup' => $this->t('<br />'),
    ];

    $form['mcd_config_install_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => 'Install Module Config',
      '#description' => t('Installs default config from the module entered above.'),
    ];

    $form['mcd_config_install_wrapper']['spacer_1'] = [
      '#type' => 'item',
      '#markup' => $this->t('<br />'),
    ];

    $form['mcd_config_install_wrapper']['remove_config'] = [
      '#type' => 'submit',
      '#value' => $this->t('Install Module Config'),
      '#submit' => ['::installModuleConfig'],
    ];

    $form['mcd_config_install_wrapper']['spacer_2'] = [
      '#type' => 'item',
      '#markup' => $this->t('<br />'),
    ];
    
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function testRemovalCallback(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();
    $configList = [];
    $config_path = drupal_get_path('module', $values['mcd_wrapper']['module_name']) . $values['mcd_wrapper']['directory_path'];

    if (is_dir($config_path)) {
      if ($dh = opendir($config_path)) {
        while (($fileName = readdir($dh)) !== false) {
          if (pathinfo($fileName, PATHINFO_EXTENSION) == 'yml') {
            $fileName = str_replace('.yml', '', $fileName);
            if (!empty(\Drupal::service('config.storage')->read($fileName))) {
              $configList[] = $fileName;
            }
            
          }
        }
        closedir($dh);
      }
    }

    asort($configList);

    $markup = new TranslatableMarkup(
      '<fieldset>@title<ul>@li</ul>@noModule@configExists@noConfigExists</fieldset>', [
        '@title' => count($configList) > 0 ? t('<p>Active Config List:</p>') : '',
        '@li' => count($configList) > 0 ? t('<li>' . implode('</li><li>', $configList) . '</li>') : '',
        '@noModule' => drupal_get_path('module', $values['mcd_wrapper']['module_name']) ? '' : t('No module found with that name.'),
        '@configExists' => count($configList) > 0 ? t('<p>To delete the listed active config, use the delete button below.</p>') : '',
        '@noConfigExists' => count($configList) == 0 && drupal_get_path('module', $values['mcd_wrapper']['module_name']) != NULL ? t('No active config found.') : '',
      ]
    );

    $form['mcd_testing_wrapper']['config_files_list']['#description'] = $markup;
    return $form['mcd_testing_wrapper'];
  }

 /**
  * {@inheritdoc}
  */
  public static function testRemoveModuleConfig(array &$form, FormStateInterface $form_state) {

  }

  /**
  * {@inheritdoc}
  */
  public function removeModuleConfig(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();
    $config_path = drupal_get_path('module', $values['mcd_wrapper']['module_name']) . $values['mcd_wrapper']['directory_path'];

    if (is_dir($config_path)) {
      if ($dh = opendir($config_path)) {
        while (($fileName = readdir($dh)) !== false) {
          if (pathinfo($fileName, PATHINFO_EXTENSION) == 'yml') {
            $fileNameClean = str_replace('.yml', '', $fileName);
            \Drupal::configFactory()->getEditable($fileNameClean)->delete();
            \Drupal::messenger()->addMessage($fileName . ' config file has been deleted.', MessengerInterface::TYPE_STATUS);
          }
        }
        closedir($dh);
      }
    }
    
  }

  /**
  * {@inheritdoc}
  */
  public function installModuleConfig(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();
    \Drupal::service('config.installer')->installDefaultConfig('module', $values['mcd_wrapper']['module_name']);
    \Drupal::messenger()->addMessage('The ' . $values['mcd_wrapper']['module_name'] . ' module config has been installed.', MessengerInterface::TYPE_STATUS);

  }

}
