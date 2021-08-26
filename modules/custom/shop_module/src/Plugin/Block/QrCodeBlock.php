<?php

namespace Drupal\shop_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'QrCodeBlock' Block.
 *
 * @Block(
 *   id = "qrcode_block",
 *   admin_label = @Translation("QR Code block"),
 *   category = @Translation("Shop module"),
 * )
 */
class QrCodeBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => $this->printQrCode(),
    ];
  }

  private function printQrCode(){

q    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
        $nid = $node->id();
    }else{
        return true;
    }

    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $node = $node_storage->load($nid);
    $node->field_app_purchase_link->value;
    echo $node->field_app_purchase_link->uri;
    
  }


}