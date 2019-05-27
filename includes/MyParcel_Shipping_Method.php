<?php declare(strict_types=1);

use MyParcelCom\ApiSdk\Resources\Address;
use MyParcelCom\ApiSdk\Shipments\PriceCalculator;
use MyParcelCom\ApiSdk\Resources\Shipment;

class MyParcel_Shipping_Method extends WC_Shipping_Method 
{

    /**
     *
     * @return void
     */
    public function __construct()
    {
        $this->id = 'myparcel';
        $this->method_title = __('MyParcel.com Shipping', 'myparcel');
        $this->method_description = __('Custom Shipping Method for MyParcel.com', 'myparcel');
        $this->init();
        $this->enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : 'yes';
        $this->title = isset($this->settings['title']) ? $this->settings['title'] : __('MyParcel Shipping', 'myparcel');
    }

    /**
     *
     * @return void
     */
    public function init(): void
    {
        $this->init_form_fields(); 
        $this->init_settings(); 
        add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
    }

    /**
     *
     * @return void 
     */
    function init_form_fields(): void
    {
        $this->form_fields = [

            'enabled' => [
                'title' => __('Enable', 'myparcel'),
                'type' => 'checkbox',
                'description' => __('Enable this shipping.', 'myparcel'),
                'default' => 'yes'
            ],

            'title' => [
                'title' => __('Title', 'myparcel'),
                'type' => 'text',
                'description' => __('Dummy text', 'myparcel'),
                'default' => __('MyParcel.com Shipping', 'myparcel')
            ],
        ];
    }



    public function calculate_shipping($package=array()){ 
 
          // This is where you'll add your rates
           
            $this->add_rate( array(
              'id'  => $this->id,
              'label' => $this->title,
              'cost'    => '50'
            ));
             // This will add custom cost to shipping method 
    }

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
    
}

