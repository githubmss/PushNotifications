# PushNotifications
PushNotification For App



Mmsbuilder is a fully functional mobile native application(iOS/Android) for the online stores based on Magento platform. All you need to do is, configure your store with the app and it is ready to be used by your customers. The mobile app developed using native platforms which transform your eCommerce business into a fresh shopping experience on all mobile devices with stunning designs. Not just an app, Mms builder offers a complete package that does not only bring the better way for your users to experience your Magento store but also make your store more professional with a friendly interface with smooth navigation and awesome animation effects, stunning design and unique for Magento2 based store.

[![Stable Version!]:Magento 2.2

## 1. Documentation

    User guide: https://web7.staging02.com/demo2/pub/media/User%20Guide.pdf


## 2. How to install Mmsbuilder Extension

### Install via composer (recommend)

Run the following command in Magento 2 root folder:

```
composer require mmsbuilder/pushnotification
php bin/magento setup:di:compile
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
sudo chmod  0777 -R  var/*
sudo chmod  0777 -R  generated/*
sudo chmod  0777 -R  pub/static/*
```
It will install module in vendor directory with name mmsbuilder/pushnotification






