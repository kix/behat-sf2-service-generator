<?php
namespace Kix\Symfony2ServiceExtension\ExceptionObserver;

use Kix\ExceptionListenerExtension\Observer\ExceptionObserver;
use RMiller\PhpSpecExtension\Process\DescRunner;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Dumper\YamlDumper;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class NonExistentServiceObserver implements ExceptionObserver
{

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var DescRunner
     */
    private $specRunner;

    public function __construct(OutputInterface $output, DescRunner $specRunner)
    {
        $this->output = $output;
        $this->specRunner = $specRunner;
    }

    public function notify(\Exception $exception)
    {
        if (!$exception instanceof ServiceNotFoundException) {
            return;
        }

        $serviceId = $exception->getId();
        $guessedFqcn = $this->guessFqcn($serviceId);

        $definition = new Definition();
        $definition->setClass($guessedFqcn);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions([
            $serviceId => $definition
        ]);

        $dumper = new YamlDumper($containerBuilder);
        $result = $dumper->dump();

        $message = sprintf('Service `%s` missing. Define it in your services.yml:', $serviceId);

        $this->output->writeln('--- ' . $message . PHP_EOL);

        $this->output->write($result, true);

        $errorMessages = [
            'Service ' . $serviceId . ' was not found.'
        ];

        $formatter = new FormatterHelper();
        $formattedBlock = $formatter->formatBlock($errorMessages, 'error', true);
        $this->output->writeln('');
        $this->output->writeln($formattedBlock);
        $this->output->writeln('');

        $question = sprintf('<question>Do you want to create a specification for %s? (Y/n)</question>', $guessedFqcn);

        $dialog = new DialogHelper();

        if ($dialog->askConfirmation($this->output, $question, true)) {
            $this->specRunner->runDescCommand($guessedFqcn);
        }
    }

    private function guessFqcn($id)
    {
        $list = array_map('ucfirst', explode('_', str_replace('.', '_', $id)));

        $list[1] = $list[1] . 'Bundle\Service';

        return implode('\\', $list);
    }

}