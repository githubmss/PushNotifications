<?php
namespace Mmsbuilder\Pushnotification\Model;
use Magento\Framework\Logger\Monolog;
use Mmsbuilder\Pushnotification\Helper\Data;
use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

class Generation extends Command
{
    protected $helper;
    protected $logger;
    public function __construct(Data $helper,Monolog $log)
    {
        $this->helper = $helper;
        $this->logger = $log;
        parent::__construct();

    }

    protected function configure()
    {
        $this->addArgument('device', InputArgument::REQUIRED, 'Device name?')
            ->addArgument('message', InputArgument::REQUIRED, 'What message you want to send ?')
            ->setName('generation:notification')
            ->setDescription('The description of you command here!Push Notification command.');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $input->getArgument('message');
        $device  = $input->getArgument('device');
        $this->logger->addDebug($message);
        $type    = '';
        if (!in_array($device, array('android', 'ios', 'both'))) {
            $output->writeln('Device name should be android,ios or both');
            exit();
        }
        switch ($device) {
            case 'android':
                $type = 1;
                break;
            case 'ios':
                $type = 2;
                break;
            default:
                $type = 0;
                break;
        }
        $output->writeln($message . '!');
        $response = $this->helper->sendPushNotifications($type, $message);
        print_r($response);
        $output->writeln('Notification has been sent successfully.');
    }

}
