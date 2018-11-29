<?php
 
namespace Mmsbuilder\Pushnotification\Model\Config\Backend;
 
class CustomFileType extends \Magento\Config\Model\Config\Backend\File
{
    /**
     * @return string[]
     */
    protected function _getAllowedExtensions() {
       return ['pem'];
    }
}
