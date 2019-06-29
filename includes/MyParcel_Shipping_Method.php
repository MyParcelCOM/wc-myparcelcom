<?php declare(strict_types=1);

use MyParcelCom\ApiSdk\Resources\Address;
use MyParcelCom\ApiSdk\Shipments\PriceCalculator;
use MyParcelCom\ApiSdk\Resources\Shipment;

if ( ! defined( 'WPINC' ) ) { 
    die; 
} 
/*
 * Check if WooCommerce is active
 */
  if ( ! class_exists( 'MyParcel_Shipping_Method' ) ) {
      class MyParcel_Shipping_Method extends WC_Shipping_Method {
          /**
           * Constructor for your shipping class
           *
           * @access public
           * @return void
           */
          public function __construct() {
              $this->id                 = 'myparcel'; 
              $this->method_title       = __( 'MyParcel.com Shipping', 'myparcel' );  
              $this->method_description = __( 'Custom Shipping Method for MyParcel.com', 'myparcel' ); 

              // Availability & Countries
              $this->availability = 'including';
              $this->countries = array(
                  'US', // Unites States of America
                  'CA', // Canada
                  'DE', // Germany
                  'GB', // United Kingdom
                  'IT',   // Italy
                  'ES', // Spain
                  'HR'  // Croatia
                  );

              $this->init();
              $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
              $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'MyParcel.com Shipping', 'myparcel' );
          }

          /**
           * Init your settings
           *
           * @access public
           * @return void
           */
          function init() {
              // Load the settings API
              $this->init_form_fields(); 
              $this->init_settings(); 

              // Save settings in admin if you have any defined
              add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
          }

          /**
           * Define settings field for this shipping
           * @return void 
           */
          function init_form_fields() { 

              $this->form_fields = array(

               'enabled' => array(
                    'title' => __( 'Enable', 'myparcel' ),
                    'type' => 'checkbox',
                    'description' => __( 'Enable this shipping.', 'myparcel' ),
                    'default' => 'yes'
                    ),

               'title' => array(
                  'title' => __( 'Title', 'myparcel' ),
                    'type' => 'text',
                    'description' => __( 'Title to be display on site', 'myparcel' ),
                    'default' => __( 'MyParcel.com Shipping', 'myparcel' )
                    ),

               'weight' => array(
                  'title' => __( 'Weight (kg)', 'myparcel' ),
                    'type' => 'number',
                    'description' => __( 'Maximum allowed weight', 'myparcel' ),
                    'default' => 100
                    ),

               'price' => array(
                  'title' => __( 'Cost', 'myparcel' ),
                    'type' => 'number',
                    'description' => __( 'MyParcel.com Shipping Cost', 'myparcel' ),
                    'default' => 50
                    ),

               );
          }
          /**
           * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
           *
           * @access public
           * @param mixed $package
           * @return void
           */
          public function calculate_shipping( $package ) {                            
            $myparcelSetting = get_option('woocommerce_myparcel_settings');                
            $myparcelCost = ( $myparcelSetting['price'] ) ? $myparcelSetting['price'] : 50;                
            $this->add_rate( array(
              'id'  => $this->id,
              'label' => $this->title,
              'cost'    => $myparcelCost
            ));
          }
      }
  } 
    
 
    
 
 
 