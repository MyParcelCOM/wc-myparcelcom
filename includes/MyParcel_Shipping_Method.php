<?php declare(strict_types=1);

use MyParcelCom\ApiSdk\Resources\Address;
use MyParcelCom\ApiSdk\Shipments\PriceCalculator;
use MyParcelCom\ApiSdk\Resources\Shipment;


// class MyParcel_Shipping_Method extends WC_Shipping_Method 
// {

//     /**
//      *
//      * @return void
//      */
//     public function __construct()
//     {
//         $this->id = 'myparcel';
//         $this->method_title = __('MyParcel.com Shipping', 'myparcel');
//         $this->method_description = __('Custom Shipping Method for MyParcel.com', 'myparcel');
//         $this->init();
//         $this->enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : 'yes';
//         $this->title = isset($this->settings['title']) ? $this->settings['title'] : __('MyParcel Shipping', 'myparcel');
//     }

//     /**
//      *
//      * @return void
//      */
//     public function init(): void
//     {
//         $this->init_form_fields(); 
//         $this->init_settings(); 
//         add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
//     }

//     /**
//      *
//      * @return void 
//      */
//     function init_form_fields(): void
//     {
//         $this->form_fields = [

//             'enabled' => [
//                 'title' => __('Enable', 'myparcel'),
//                 'type' => 'checkbox',
//                 'description' => __('Enable this shipping.', 'myparcel'),
//                 'default' => 'yes'
//             ],

//             'title' => [
//                 'title' => __('Title', 'myparcel'),
//                 'type' => 'text',
//                 'description' => __('Dummy text', 'myparcel'),
//                 'default' => __('MyParcel.com Shipping', 'myparcel')
//             ],
//         ];
//     }



//     public function calculate_shipping($package=array()){ 
 
//           // This is where you'll add your rates
           
//             $this->add_rate( array(
//               'id'  => $this->id,
//               'label' => $this->title,
//               'cost'    => '50'
//             ));
//              // This will add custom cost to shipping method 
//     }

    /**
     * @param mixed $package
     *
     * @return void
     */
    // public function calculate_shipping($package = array()): void
    // {
    //     $weight = 0;
    //     $cost   = 0;
    //     $transitTime = '';
    //     $country = $package["destination"]["country"];
    //     $productUnit = get_option('woocommerce_weight_unit');

    //     foreach ($package['contents'] as $item_id => $values) {
    //         $_product = $values['data']; 
    //         $weight = $weight + $_product->get_weight() * $values['quantity']; 
    //     }

    //     $weight = wc_get_weight($weight, 'g',$productUnit);
    //     $object = new MyParcel_API();
    //     $mainObj = $object->apiAuthentication();
    //     $recipient = new Address();
    //     echo "<pre>";
    //     print_r($recipient); die;
    //     $recipient->setCountryCode($country);
    //     $shipment = new Shipment();


    //     $shipment->setRecipientAddress($recipient)->setWeight($weight, 'grams');
    //     $services = $mainObj->getServices($shipment);
    //     $carriers = $mainObj->getCarriers()->get();

    //     if (!empty($services)) {
    //         foreach ($services as $service) {
    //             $carrierRelation = $service->getCarrier();
    //             $carrierId = $carrierRelation->getId();
    //             $carrierArray = array_filter($carriers, function ($carrier) use ($carrierId) {
    //                 return $carrier->getId() === $carrierId;
    //             });
    //             $carrierObj   = reset($carrierArray);
    //             $carrierName  = $carrierObj->getName();
    //             $minTime = $service->getTransitTimeMin();
    //             $maxTime = $service->getTransitTimeMax();
                
    //             if (isset($minTime) && !empty($minTime) && isset($maxTime) && !empty($maxTime)) {
    //                 $transitTime = $service->getTransitTimeMin() .' - '.$service->getTransitTimeMax().' days';
    //             } elseif (isset($minTime) && !empty($minTime)) {
    //                 $transitTime = $service->getTransitTimeMin().' days'; 
    //             }

    //             // $contracts = $service->getServiceContracts();
    //             // $contract = $contracts[0];

    //             $calculator = new PriceCalculator();
    //             // Calculate the price for given service contract.
    //             $cost = $calculator->calculate($shipment, $contract);
    //             // $cost = $calculator->calculate($shipment);
    //             $rate = [
    //             'id' => $service->getId(),
    //             'label' => $service->getDeliveryMethod(),
    //             'cost' => $cost,
    //             'meta_data' => ['delivery_method' => $service->getName(), 'carrier_name' => $carrierName, 'transit_time' => $transitTime, 'line_2' => '100 bridge, Southtukoganj Indore']
    //             ];
    //             $this->add_rate($rate);

    //         }

    //     }

    // }
    
// }

if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}
 
/*
 * Check if WooCommerce is active
 */
// if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
    // function myparcel_shipping_method() {
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
                    
                    // $weight = 0;
                    // $cost = 0;
                    // $country = $package["destination"]["country"];
 
                    // foreach ( $package['contents'] as $item_id => $values ) 
                    // { 
                    //     $_product = $values['data']; 
                    //     $weight = $weight + $_product->get_weight() * $values['quantity']; 
                    // }
 
                    // $weight = wc_get_weight( $weight, 'kg' );
 
                    // if( $weight <= 10 ) {
 
                    //     $cost = 0;
 
                    // } elseif( $weight <= 30 ) {
 
                    //     $cost = 5;
 
                    // } elseif( $weight <= 50 ) {
 
                    //     $cost = 10;
 
                    // } else {
 
                    //     $cost = 20;
 
                    // }
 
                    // $countryZones = array(
                    //     'HR' => 0,
                    //     'US' => 3,
                    //     'GB' => 2,
                    //     'CA' => 3,
                    //     'ES' => 2,
                    //     'DE' => 1,
                    //     'IT' => 1
                    //     );
 
                    // $zonePrices = array(
                    //     0 => 10,
                    //     1 => 30,
                    //     2 => 50,
                    //     3 => 70
                    //     );
 
                    // $zoneFromCountry = $countryZones[ $country ];
                    // $priceFromZone = $zonePrices[ $zoneFromCountry ];
 
                    // $cost += $priceFromZone;
 
                    // $rate = array(
                    //     'id' => $this->id,
                    //     'label' => $this->title,
                    //     'cost' => $cost
                    // );
 
                    // $this->add_rate( $rate );
                    //           // This is where you'll add your rates
                $myparcelSetting = get_option('woocommerce_myparcel_settings');                
                $myparcelCost = ( $myparcelSetting['price'] ) ? $myparcelSetting['price'] : 50;                
                $this->add_rate( array(
                  'id'  => $this->id,
                  'label' => $this->title,
                  'cost'    => $myparcelCost
                ));
             // This will add custom cost to shipping method 
                    
                }
            }
        }
    // }
 
    //add_action( 'woocommerce_shipping_init', 'myparcel_shipping_method' );
 
    function add_myparcel_shipping_method( $methods ) {
        $methods[] = 'MyParcel_Shipping_Method';
        return $methods;
    }
 
    // add_filter( 'woocommerce_shipping_methods', 'add_myparcel_shipping_method' );
 
    function myparcel_validate_order( $posted )   {
 
        $packages = WC()->shipping->get_packages();
 
        $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
         
        if( is_array( $chosen_methods ) && in_array( 'myparcel', $chosen_methods ) ) {
             
            foreach ( $packages as $i => $package ) {
 
                if ( $chosen_methods[ $i ] != "myparcel" ) {
                             
                    continue;
                             
                }
 
                $MyParcel_Shipping_Method = new MyParcel_Shipping_Method();
                $weightLimit = (int) $MyParcel_Shipping_Method->settings['weight'];
                $weight = 0;
 
                foreach ( $package['contents'] as $item_id => $values ) 
                { 
                    $_product = $values['data']; 
                    $weight = $weight + $_product->get_weight() * $values['quantity']; 
                }
 
                $weight = wc_get_weight( $weight, 'kg' );
                
                if( $weight > $weightLimit ) {
 
                        $message = sprintf( __( 'Sorry, %d kg exceeds the maximum weight of %d kg for %s', 'myparcel' ), $weight, $weightLimit, $MyParcel_Shipping_Method->title );
                             
                        $messageType = "error";
 
                        if( ! wc_has_notice( $message, $messageType ) ) {
                         
                            wc_add_notice( $message, $messageType );
                      
                        }
                }
            }       
        } 
    }
 
    //add_action( 'woocommerce_review_order_before_cart_contents', 'myparcel_validate_order' , 10 );
    //add_action( 'woocommerce_after_checkout_validation', 'myparcel_validate_order' , 10 );
// }