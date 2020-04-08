<?php

namespace Boot\Foundation\Bootstrappers;

use App\Support\Console\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

class LoadConsoleEnvironment extends Bootstrapper
{
    public function beforeBoot()
    {
        Command::setup($this->app);

        $command_arguments = $_SERVER['argv'];
        $command_namespace = data_get($_SERVER, 'argv.1');

        Command::app()->bind(ConsoleOutput::class, new ConsoleOutput(
            config('console.output.verbosity'),
            config('console.output.decorated'),
            config('console.output.formatter')
        ));

        Command::app()->bind('arguments', $command_arguments);
        Command::app()->bind('namespace', $command_namespace);

        $resolve_input = fn (InputDefinition $options) => new ArgvInput([], Command::bindDefinition());

        Command::app()->bind(ArgvInput::class, $resolve_input);
        Command::app()->bind(InputInterface::class, fn (ArgvInput $input) => $input);
    }

    public function boot()
    {
        collect($this->kernel->commands)->each(function ($handle) {
            $command = new $handle;

            Command::define($command->namespace, $command->arguments())->run($handle);
        });
    }
}