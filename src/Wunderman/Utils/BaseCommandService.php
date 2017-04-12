<?php
namespace Wunderman\Utils;

class BaseCommandService
{
    /**
     * Logger
     * @var Logger
     */
    protected $logger;

    /**
     * Sortie console 
     * @var OutputInterface
     */
    protected $output=null;


    /**
     * Affichage du titre
     *
     * @param $value
     */
    protected function logTitle($value){
        // via monolog
        $this->logger->notice($value);
        
        // Retour console si possible
        if($this->output){
            $this->output->writeln("");  
            $this->output->writeln(sprintf('<options=bold>%s</options=bold>', $value));    
             
            $this->output->writeln(sprintf('<fg=yellow;options=bold>%s</fg=yellow;options=bold>', str_repeat("-",63)));  
            $this->output->writeln("");
        }
    }

    /**
     * Affichage de message d'info
     *
     * @param $value
     */
    protected function logInfo($value, $chevron = '>'){
        /*
        // via monolog
        $this->logger->notice($value);

        // Retour console si possible
        if($this->output){
           $this->output->writeln(sprintf('  <comment>%s</comment> <info>%s</info>', $chevron, $value));
        }
        */
    }

    /**
     * Affichage des erreurs
     *
     * @param $value
     */
    protected function logError($value, $chevron = '>'){
        // via monolog
        $this->logger->error($value);
        
        // Retour console si possible
        if($this->output){
            $this->output->writeln(sprintf('  <comment>%s</comment> <error>%s</error>', $chevron, $value));
        }
    }
}