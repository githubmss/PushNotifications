<?php
namespace Mmsbuilder\Pushnotification\Block;
 
class Hello extends \Magento\Framework\View\Element\Template
{
    public function getHelloWorldTxt()
    {
        return 'Test Push Notification!';
    }
}