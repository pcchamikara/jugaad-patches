<?php
      
//'#markup' => $this->printQrCode(),

namespace Drupal\shop_module\Plugin\Block;
use chillerlan\QRCode\QRCode;;
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

    $qr_data = $this->printQrCode();

    return [
      '#theme' => 'qr_code_block',
      '#qr_code_data' => $qr_data,
      '#cache' => [
        'max-age' => 0,
      ]
    ];
  }

  /**
   * Return the QR code image component.
   *
  */
  private function printQrCode(){

    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {

      $node->field_app_purchase_link->value;
      $purchase_link = $node->field_app_purchase_link->uri;
      $qr_code =  $this->getQrcode($purchase_link);
      return $qr_code;

    }

  }

  /**
 * Return the QR code.
 *
 *
 * @param $url
 *  The QR code to return the URI for.
 *
 * @return
 * '$qr_code'as A SVG  
 *
 */
  private function getQrcode($url){
    if( $url != '' ){
      $data = 'otpauth:'.$url;
      $qr_code = new QRCode;
      $qr_code = $qr_code->render($data);
      return $qr_code;
    }else{
      return false;
    }
    
  }


}