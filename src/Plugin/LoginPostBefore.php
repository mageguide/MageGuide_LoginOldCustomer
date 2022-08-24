<?php

namespace MageGuide\LoginOldCustomer\Plugin;

use Magento\Customer\Controller\Account\LoginPost;

class LoginPostBefore
{
    protected $_sessions;
    protected $_customer;
    protected $_customer_model;
    protected $_customerAccountManagement;
    protected $messageManager;
    protected $_resource;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Backend\Model\Session $sessions,
        \Magento\Customer\Model\Session $customer,
        \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\Customer $customer_model
    ) {
        $this->_sessions = $sessions;
        $this->_customer = $customer;
        $this->_customer_model = $customer_model;
        $this->_request = $context->getRequest();
        $this->_response = $context->getResponse();
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->resultFactory = $context->getResultFactory();
        $this->_customerAccountManagement = $customerAccountManagement;
        $this->messageManager = $messageManager;
        $this->_resource = $resource;
    }


    public function aroundExecute(\Magento\Customer\Controller\Account\LoginPost $subject, $proceed)
    {
        //before           
        $login =  $this->_request->getPost('login');

        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('customer_entity_varchar');

        $writer = new \Zend\Log\Writer\Stream(BP.'/var/log/login-old-customers-log.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        

        if (!empty($login['username'])) { // && !empty($login['password'])

            $logger->info($login['username']);
           
            $this->_customer_model->setWebsiteId(1);
            $this->_customer_model->loadByEmail($login['username']);
            $userId = $this->_customer_model->getId();
            $old_customer_code = $this->_customer_model->getOldCustomerCode();
            $flag_is_old = $this->_customer_model->getFlagIsOld();
            if($old_customer_code && $flag_is_old == '1'){
                $logger->info($this->_customer_model->getEmail());
                $this->_customerAccountManagement->initiatePasswordReset(
                    $this->_customer_model->getEmail(),
                    $this->_customerAccountManagement::EMAIL_RESET,
                    $this->_customer_model->getWebsiteId()
                );                

                //Update Data into table without saving customer
                $sql = "UPDATE " . $tableName . " SET `value` = '0' WHERE `attribute_id` = '207' AND `entity_id` = '".$userId."'"; //207 is the flag_is_old attribute in this example
                $logger->info($sql);
                $connection->query($sql);

                //Update Data by saving customer
                //$this->_customer_model->setFlagIsOld('0');
                //$this->_customer_model->save();
            }
        }
        

        $returnValue = $proceed();            

        // after
        
        if (!empty($login['username'])) {
            if($old_customer_code && $flag_is_old == '1'){
                $logger->info('clear');
                $this->messageManager->getMessages(true);
                $this->messageManager->addWarning( __('Welcome to the new website of examplemageguide.com! In order to login you have to follow the instructions we just sent you via e-mail about reset password') );
                //$this->messageManager->addWarning( __('Καλωσήρθες στο νέο site του examplemageguide.com! Για να συνδεθείς θα πρέπει να ακολουθήσεις τις οδηγίες που μόλις σου στείλαμε μέσω e-mail για ανάκτηση κωδικού!') ); //greek version of message
            }
        }

        return $returnValue;
    }

}