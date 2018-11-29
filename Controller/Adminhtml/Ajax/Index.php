<?php
namespace Mmsbuilder\Pushnotification\Controller\Adminhtml\Ajax;

use Mmsbuilder\Pushnotification\Helper\Data;
use Magento\Framework\App\Action\Context;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $helper;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper            = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->directory_list    = $directory_list;
        return parent::__construct($context);
    }

    public function execute()
    {
        if ($this->getRequest()->getPost('message')) {
            $type    = $this->getRequest()->getPost('type');
            $message = $this->getRequest()->getPost('message');
            if ($this->helper->getBackground()) {
                $this->helper->msgConsole($type, $message);
                $response['statusCode'] = 200;
                $response['type']       = $type;
                $response['msg']        = 'Notification is sending through console.';
                $result = $this->resultJsonFactory->create();
                return $result->setData($response);
            } else {
                $response = $this->helper->sendPushNotifications($type, $message);
                $result   = $this->resultJsonFactory->create();
                if (empty($response)) {
                    return $result->setData(array('statusCode' => 400, 'msg' => 'There is something wrong to process your request.'));
                } else {
                    return $result->setData($response);
                }
            }
        } else {
            $result = $this->resultJsonFactory->create();
            return $result->setData(array('statusCode' => 400, 'msg' => 'There is something wrong to process your request.'));
        }
    }
}
