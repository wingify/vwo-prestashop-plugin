<?php   
if (!defined('_PS_VERSION_'))
  exit;

class VisualWebsiteOptimizer extends Module
{
    public function __construct()
    {
        $this->name = 'visualwebsiteoptimizer';
        $this->tab = 'advertising_marketing';
        $this->version = '1.0.0';
        $this->author = 'VWO';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5.6.1', 'max' => _PS_VERSION_); 
        $this->bootstrap = true;
     
        parent::__construct();
     
        $this->displayName = $this->l('Visual Website Optimizer');
        $this->description = $this->l('Installs VWO tracking code to your PrestaShop. It also enables you to track revenue generated without any code changes.');
     
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
     
        if (!Configuration::get('ACCOUNT_ID'))      
          $this->warning = $this->l('No account id provided');
    }

    public function install()
    {   
        if (Shop::isFeatureActive())
        Shop::setContext(Shop::CONTEXT_ALL);

        Configuration::updateValue('TYPE_VWO_SMART_CODE', 2);
        Configuration::updateValue('SETTINGS_TOLERANCE', 2000);
        Configuration::updateValue('LIBRARY_TOLERANCE', 2500);
        Configuration::updateValue('TYPE_VWO_REVENUE_TRACKING', 1);
        Configuration::updateValue('IS_REVENUE_TRACKING', 1);
        if (!parent::install() ||
        !$this->registerHook('header') ||
        !$this->registerHook('orderConfirmation')
        )
        return false;

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||
        !Configuration::deleteByName('ACCOUNT_ID') ||
        !Configuration::deleteByName('TYPE_VWO_SMART_CODE') ||
        !Configuration::deleteByName('TYPE_VWO_REVENUE_TRACKING') ||
        !Configuration::deleteByName('IS_REVENUE_TRACKING')
        )
        return false;

        return true;
    }

    public function getContent()
    {
        $output = null;
     
        if (Tools::isSubmit('submit'.$this->name))
        {
            $account_id = strval(Tools::getValue('ACCOUNT_ID'));
            $type_of_smart_code = Tools::getValue('TYPE_VWO_SMART_CODE');
            $library_tolerance = strval(Tools::getValue('LIBRARY_TOLERANCE'));
            $settings_tolerance = strval(Tools::getValue('SETTINGS_TOLERANCE'));
            $type_of_revenue_tracking = Tools::getValue('TYPE_VWO_REVENUE_TRACKING');
            $is_revenue_tracking = Tools::getValue('IS_REVENUE_TRACKING');
            if (!$account_id
              || empty($account_id)
              || !Validate::isGenericName($account_id)
              )
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            else
            {
                Configuration::updateValue('ACCOUNT_ID', $account_id);
                Configuration::updateValue('TYPE_VWO_SMART_CODE', $type_of_smart_code);
                Configuration::updateValue('LIBRARY_TOLERANCE', $library_tolerance);
                Configuration::updateValue('SETTINGS_TOLERANCE', $settings_tolerance);
                Configuration::updateValue('TYPE_VWO_REVENUE_TRACKING', $type_of_revenue_tracking);
                Configuration::updateValue('IS_REVENUE_TRACKING', $is_revenue_tracking);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        return $output.$this->displayForm();
    }

    public function displayForm()
    {
        // Get default language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
         
        // Init Fields form array
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('VWO Account ID'),
                    'name' => 'ACCOUNT_ID',
                    'size' => 20,
                    'required' => true,
                    'class' => 'fixed-width-md',
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Type of VWO smart code'),
                    'name' => 'TYPE_VWO_SMART_CODE',
                    'hint' => $this->l('Select whether you want synchronous or asynchronous type of VWO smart code'),
                    'values' => array(
                        array(
                            'id' => 'synchronous',
                            'value' => 1,
                            'label' => $this->l('Synchronous')
                        ),
                        array(
                            'id' => 'asynchronous',
                            'value' => 2,
                            'label' => $this->l('Asynchronous')
                        ),
                    )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Setting tolerance time'),
                    'name' => 'SETTINGS_TOLERANCE',
                    'size' => 10,
                    'class' => 'fixed-width-md',
                    'suffix' => 'milliseconds',
                    'desc' => 'Settings Tolerance time for VWO asynchronous smart code, leave blank for synchronous'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Library tolerance time'),
                    'name' => 'LIBRARY_TOLERANCE',
                    'size' => 10,
                    'class' => 'fixed-width-md',
                    'suffix' => 'milliseconds',
                    'desc' => 'Library Tolerance time for VWO asynchronous smart code, leave blank for synchronous'
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Revenue Tracking'),
                    'name' => 'IS_REVENUE_TRACKING',
                    'is_bool' => true,
                    'desc' => $this->l('Activate revenue tracking with VWO'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('ENABLED')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('DISABLED')
                        )
                    ),
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Type of VWO revenue tracking'),
                    'name' => 'TYPE_VWO_REVENUE_TRACKING',
                    'hint' => $this->l('Select which type of revenue tracking you want'),
                    'values' => array(
                        array(
                            'id' => 'GrandTotal',
                            'value' => 1,
                            'label' => $this->l('Grand Total')
                        ),
                        array(
                            'id' => 'TotalwithoutShipping',
                            'value' => 2,
                            'label' => $this->l('Total - Shipping')
                        ),
                        array(
                            'id' => 'TotalwithoutShippingwithoutTax',
                            'value' => 3,
                            'label' => $this->l('Total - Shipping - Taxes')
                        ),
                    )
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'button'
            )
        );

         
        $helper = new HelperForm();
         
        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
         
        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
         
        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = array(
            'save' =>
            array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );
         
        // Load current value
        $helper->fields_value['ACCOUNT_ID'] = Tools::getValue('ACCOUNT_ID', Configuration::get('ACCOUNT_ID'));
        $helper->fields_value['TYPE_VWO_SMART_CODE'] = Tools::getValue('TYPE_VWO_SMART_CODE', Configuration::get('TYPE_VWO_SMART_CODE'));
        $helper->fields_value['SETTINGS_TOLERANCE'] = (int)Tools::getValue('SETTINGS_TOLERANCE', Configuration::get('SETTINGS_TOLERANCE'));
        $helper->fields_value['LIBRARY_TOLERANCE'] = (int)Tools::getValue('LIBRARY_TOLERANCE', Configuration::get('LIBRARY_TOLERANCE'));
        $helper->fields_value['IS_REVENUE_TRACKING'] = Tools::getValue('IS_REVENUE_TRACKING', Configuration::get('IS_REVENUE_TRACKING'));
        $helper->fields_value['TYPE_VWO_REVENUE_TRACKING'] = Tools::getValue('TYPE_VWO_REVENUE_TRACKING', Configuration::get('TYPE_VWO_REVENUE_TRACKING'));

        return $helper->generateForm($fields_form);
    }

    protected function _getAsyncVisualWebOptimizerTag()
    {   
        $current_page = $_SERVER["REQUEST_URI"];
        // $current_page = $this->context->link->getPageLink(Context::getContext()->controller->php_self);
        // need to find a better way to do this
                // Context

        $context = Context::getContext();
        $controller = Context::getContext()->controller->php_self;
        if ($controller == "category") {
            $cid = $context->controller->getCategory()->id;
            $cat_link = $context->link->getCategoryLink($cid);
            return "
            <!-- Start Visual Website Optimizer Asynchronous Code -->
            <script type='text/JavaScript'>
            var _vwo_url_prefix = '" .$cat_link."'; 
            var _vis_opt_url = _vwo_url_prefix;
            window._vwo_code = window._vwo_code || (function(){
            _vis_opt_url = window._vis_opt_url || document.URL;
            var account_id=".Tools::safeOutput(Configuration::get('ACCOUNT_ID')).",
            settings_tolerance=".Tools::getValue('SETTINGS_TOLERANCE', Configuration::get('SETTINGS_TOLERANCE')).",
            library_tolerance=".Tools::getValue('LIBRARY_TOLERANCE', Configuration::get('LIBRARY_TOLERANCE')).",
            use_existing_jquery=false,
            is_spa=1,
            hide_element='body',
            // DO NOT EDIT BELOW THIS LINE
            f=false,d=document,code={use_existing_jquery:function(){return use_existing_jquery;},library_tolerance:function(){return library_tolerance;},finish:function(){if(!f){f=true;var a=d.getElementById('_vis_opt_path_hides');if(a)a.parentNode.removeChild(a);}},finished:function(){return f;},load:function(a){var b=d.createElement('script');b.src=a;b.type='text/javascript';b.innerText;b.onerror=function(){_vwo_code.finish();};d.getElementsByTagName('head')[0].appendChild(b);},init:function(){
window.settings_timer=setTimeout('_vwo_code.finish()',settings_tolerance);var a=d.createElement('style'),b=hide_element?hide_element+'{opacity:0 !important;filter:alpha(opacity=0) !important;background:none !important;}':'',h=d.getElementsByTagName('head')[0];a.setAttribute('id','_vis_opt_path_hides');a.setAttribute('type','text/css');if(a.styleSheet)a.styleSheet.cssText=b;else a.appendChild(d.createTextNode(b));h.appendChild(a);this.load('https://dev.visualwebsiteoptimizer.com/j.php?a='+account_id+'&u='+encodeURIComponent(d.URL)+'&f='+(+is_spa)+'&r='+Math.random());return settings_timer; }};window._vwo_settings_timer = code.init(); return code; }());
</script>
<!-- End VWO Async Smartcode -->";
        }
        else if ($controller == "product") {
            $pid = $context->controller->getProduct()->id;
            $prod_link = $context->link->getProductLink($pid);
           return "
            <!-- Start Visual Website Optimizer Asynchronous Code -->
            <script type='text/JavaScript'>
            var _vwo_url_prefix = '" .$cat_link."'; 
            var _vis_opt_url = _vwo_url_prefix;
            window._vwo_code = window._vwo_code || (function(){
            _vis_opt_url = window._vis_opt_url || document.URL;
            var account_id=".Tools::safeOutput(Configuration::get('ACCOUNT_ID')).",
            settings_tolerance=".Tools::getValue('SETTINGS_TOLERANCE', Configuration::get('SETTINGS_TOLERANCE')).",
            library_tolerance=".Tools::getValue('LIBRARY_TOLERANCE', Configuration::get('LIBRARY_TOLERANCE')).",
            use_existing_jquery=false,
            is_spa=1,
            hide_element='body',
            // DO NOT EDIT BELOW THIS LINE
            f=false,d=document,code={use_existing_jquery:function(){return use_existing_jquery;},library_tolerance:function(){return library_tolerance;},finish:function(){if(!f){f=true;var a=d.getElementById('_vis_opt_path_hides');if(a)a.parentNode.removeChild(a);}},finished:function(){return f;},load:function(a){var b=d.createElement('script');b.src=a;b.type='text/javascript';b.innerText;b.onerror=function(){_vwo_code.finish();};d.getElementsByTagName('head')[0].appendChild(b);},init:function(){
window.settings_timer=setTimeout('_vwo_code.finish()',settings_tolerance);var a=d.createElement('style'),b=hide_element?hide_element+'{opacity:0 !important;filter:alpha(opacity=0) !important;background:none !important;}':'',h=d.getElementsByTagName('head')[0];a.setAttribute('id','_vis_opt_path_hides');a.setAttribute('type','text/css');if(a.styleSheet)a.styleSheet.cssText=b;else a.appendChild(d.createTextNode(b));h.appendChild(a);this.load('https://dev.visualwebsiteoptimizer.com/j.php?a='+account_id+'&u='+encodeURIComponent(d.URL)+'&f='+(+is_spa)+'&r='+Math.random());return settings_timer; }};window._vwo_settings_timer = code.init(); return code; }());
</script>
<!-- End VWO Async Smartcode -->";
        } else {
            return "
            <!-- Start Visual Website Optimizer Asynchronous Code -->
            <script type='text/JavaScript'>
            var _vwo_url_prefix = '" .$cat_link."'; 
            var _vis_opt_url = _vwo_url_prefix;
            window._vwo_code = window._vwo_code || (function(){
            _vis_opt_url = window._vis_opt_url || document.URL;
            var account_id=".Tools::safeOutput(Configuration::get('ACCOUNT_ID')).",
            settings_tolerance=".Tools::getValue('SETTINGS_TOLERANCE', Configuration::get('SETTINGS_TOLERANCE')).",
            library_tolerance=".Tools::getValue('LIBRARY_TOLERANCE', Configuration::get('LIBRARY_TOLERANCE')).",
            use_existing_jquery=false,
            is_spa=1,
            hide_element='body',
            // DO NOT EDIT BELOW THIS LINE
            f=false,d=document,code={use_existing_jquery:function(){return use_existing_jquery;},library_tolerance:function(){return library_tolerance;},finish:function(){if(!f){f=true;var a=d.getElementById('_vis_opt_path_hides');if(a)a.parentNode.removeChild(a);}},finished:function(){return f;},load:function(a){var b=d.createElement('script');b.src=a;b.type='text/javascript';b.innerText;b.onerror=function(){_vwo_code.finish();};d.getElementsByTagName('head')[0].appendChild(b);},init:function(){
window.settings_timer=setTimeout('_vwo_code.finish()',settings_tolerance);var a=d.createElement('style'),b=hide_element?hide_element+'{opacity:0 !important;filter:alpha(opacity=0) !important;background:none !important;}':'',h=d.getElementsByTagName('head')[0];a.setAttribute('id','_vis_opt_path_hides');a.setAttribute('type','text/css');if(a.styleSheet)a.styleSheet.cssText=b;else a.appendChild(d.createTextNode(b));h.appendChild(a);this.load('https://dev.visualwebsiteoptimizer.com/j.php?a='+account_id+'&u='+encodeURIComponent(d.URL)+'&f='+(+is_spa)+'&r='+Math.random());return settings_timer; }};window._vwo_settings_timer = code.init(); return code; }());
</script>
<!-- End VWO Async Smartcode -->";
        }
    } 

    protected function _getSyncVisualWebOptimizerTag()
    {
        $context = Context::getContext();
        $controller = Context::getContext()->controller->php_self;
        if ($controller == "category") {
            $cid = $context->controller->getCategory()->id;
            $cat_link = $context->link->getCategoryLink($cid);
            return "
            <!-- Start VWO Smartcode --> <script src='https://dev.visualwebsiteoptimizer.com/lib/".Tools::safeOutput(Configuration::get('ACCOUNT_ID')).".js'></script> <!-- End VWO Smartcode -->

            ";
        } elseif ($controller == "product") {
            $pid = $context->controller->getProduct()->id;
            $prod_link = $context->link->getProductLink($pid);
             return "
            <!-- Start VWO Smartcode --> <script src='https://dev.visualwebsiteoptimizer.com/lib/".Tools::safeOutput(Configuration::get('ACCOUNT_ID')).".js'></script> <!-- End VWO Smartcode -->

            ";
        }
        else {
             return "
            <!-- Start VWO Smartcode --> <script src='https://dev.visualwebsiteoptimizer.com/lib/".Tools::safeOutput(Configuration::get('ACCOUNT_ID')).".js'></script> <!-- End VWO Smartcode -->

            ";
        }

    }

    public function hookHeader($params)
    {
        
        if (Configuration::get('ACCOUNT_ID'))
        {
            $type_of_smart_code = (int)Tools::getValue('TYPE_VWO_SMART_CODE', Configuration::get('TYPE_VWO_SMART_CODE'));
            if (isset($type_of_smart_code) && $type_of_smart_code == 2)
                return $this->_getAsyncVisualWebOptimizerTag();
            else
                return $this->_getSyncVisualWebOptimizerTag();
        }
    }

    public function wrapOrder($id_order)
    {
        $order = new Order((int)$id_order);

        if (Validate::isLoadedObject($order))
            return array(
                'id' => $id_order,
                'revenue' => $order->total_paid,
                'shipping' => $order->total_shipping,
                'tax' => $order->total_paid_tax_incl - $order->total_paid_tax_excl,
                'customer' => $order->id_customer);
    }

    public function hookOrderConfirmation($params)
    {
        $is_revenue_tracking = (int)Tools::getValue('IS_REVENUE_TRACKING', Configuration::get('IS_REVENUE_TRACKING'));
        if ($is_revenue_tracking == 1) {
            $order = $params['objOrder'];
            if (Validate::isLoadedObject($order))
            {
                $transaction = array(
                    'id' => $order->id,
                    'revenue' => $order->total_paid,
                    'shipping' => $order->total_shipping,
                    'tax' => $order->total_paid_tax_incl - $order->total_paid_tax_excl,
                    'customer' => $order->id_customer);
                return $this->_runJs($transaction);
            }
        }
    }

    protected function _runJs($transaction)
    {
        if (Configuration::get('ACCOUNT_ID')) {
            $type_of_revenue_tracking = $is_revenue_tracking = (int)Tools::getValue('TYPE_VWO_REVENUE_TRACKING', Configuration::get('TYPE_VWO_REVENUE_TRACKING'));
            if ($type_of_revenue_tracking == 1) $revenue = $transaction['revenue'];
            elseif ($type_of_revenue_tracking == 2) $revenue = $transaction['revenue'] - $transaction['shipping'];
            else $revenue = $transaction['revenue'] - $transaction['shipping'] - $transaction['tax'];
            return "
            <!-- revenue tracking code -->
            <script type='text/javascript'>
                var _vis_opt_revenue = ". $revenue ."  ; 
                window._vis_opt_queue = window._vis_opt_queue || [];
                window._vis_opt_queue.push(function() {_vis_opt_revenue_conversion(_vis_opt_revenue);});
                window.VWO = window.VWO || [];
                                window.VWO.push(['track.revenueConversion', _vis_opt_revenue]);

            </script>";
        }
    }
    
}
