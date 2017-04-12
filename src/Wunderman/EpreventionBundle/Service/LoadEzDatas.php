<?php
namespace Wunderman\EpreventionBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\DependencyInjection\Container;
use Wunderman\EpreventionBundle\Entity\Activite;
use Wunderman\EpreventionBundle\Entity\BinomeSituationTravailRisque;
use Wunderman\EpreventionBundle\Entity\ClasseSituationTravail;
use Wunderman\EpreventionBundle\Entity\FacteurPenibilite;
use Wunderman\EpreventionBundle\Entity\Metier;
use Wunderman\EpreventionBundle\Entity\Phase;
use Wunderman\EpreventionBundle\Entity\Risque;
use Wunderman\EpreventionBundle\Entity\SituationTravail;
use Wunderman\Utils\BaseCommandService;
use Wunderman\Utils\ConvertCsvToArray;
use Wunderman\Utils\HelperText;
use Wunderman\Utils\HelperArray;

class LoadEzDatas extends BaseCommandService
{    
    /**
     * Fichiers a traiter
     * @var string
     */
    private $files = array(
                'maeva_activite.csv',
                'maeva_activite_phase.csv',
                'maeva_binome_situation_travail.csv',
                'maeva_classe_situation_travail.csv',
                'maeva_dangers.csv',
                'maeva_facteur_penibilite.csv',
                'maeva_metier_activite.csv',
                'maeva_metier.csv',
                'maeva_metier_situation.csv',
                'maeva_phase_travail_binome_situation_travail.csv',
                'maeva_phase_travail.csv',
                'maeva_situation_travail.csv',
            );

    /**
     * Thésaurus des type de risque
     */
    private $risqueTypes = array(
                'Sécurité'  => 0 ,
                'Santé'     => 1 ,
            );

    private $escapeCars = array(
                '' => ''
            );

    /**
     *
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     *
     * @var \Symfony\Component\DependencyInjection\Container
     */
    private $container;

    /**
     * Chemin vers le reprtoire contenant les fichiers a importer
     * @var string
     */
    private $basepath;

    /**
     * Constructeur
     *
     * @param EntityManager $em        [description]
     * @param Container     $container [description]
     */
    public function __construct(EntityManager $em, Container $container)
    {
        $this->em         = $em;
        $this->container  = $container;
        $this->logger     = ""; //$this->container->get('wunderman_evaluation_risque_logger');
    }

    /**
     * Chargement des données
     *
     */
    public function load($basepath, OutputInterface $output=null)
    {

        // Définition
        $this->output   = $output;
        $this->basepath = $basepath;

        // log
        $now = new \DateTime();
        //$this->logTitle('LoadEzDatas : Chargement des données MAEVA depuis les exports eZ Publish | '.$now->format('d-m-Y G:i:s'));

        // Initialisation et Opération de controle preExec
        //$this->initialize();

        // Chargement des classes de situation de travail
       // $this->loadClassesSituationsTravails();

        // Chargement des situations de travail
        //$this->loadSituationsTravails();

        // Chargement des risques
       // $this->loadRisques();

        // Chargement des Binomes Situation travail vs Risque
        //$this->loadBinomesSituationsTravailsRisques();

        // Chargement des Phases
        //$this->loadPhases();

        // Chargement des Activités
        //$this->loadActivites();

        // Chargement des Metiers
        $this->loadMetiers();

        // Chargement des Facteurs Pénbilités
       // $this->loadFacteursPenibilites();

        // Tables associatives

        // Chargement de l'association entre les activités et les phases de travail
        //$this->loadActivitesPhasesTravails();

        // Chargement de l'association entre les metiers et les activités
        //$this->loadMetiersActivites();

        // Chargement de l'association entre les metiers et les situations de travail
       // $this->loadMetiersSituationsTravails();

        // Chargement de l'association entre les phases et les binomes des situations de travail
       // $this->loadPhasesTravailsBinomesSituationsTravailsRisques();

        $now = new \DateTime();
        //$this->logTitle('Fin de traitement | '.$now->format('d-m-Y G:i:s'));
    }

    /**
     * Initialisation et Opération de controle preExec
     *
     */
    protected function initialize()
    {
        $errors=0;

        // Check des fichiers a traiter
        foreach ($this->files as $file) {
            $pathfile = $this->basepath.'/'.$file;
            if (!file_exists($pathfile)) {
                $this->logError('ERREUR : Le fichier "'.$pathfile.'" n\'existe pas');
                $errors++;
            }
        }

        if (0 < $errors) {
            exit;
        }
    }

    /**
     * Retourne la progress bar
     *
     * @return ProgressBar
     */
    protected function getProgressBar($size=0)
    {
        $progress = new ProgressBar($this->output, $size);
        $progress->setFormat('    [%bar%] %current%/%max%  %percent:3s%% - %elapsed:6s%  %memory:6s%');
        return $progress;
    }

    /**
     * Extraction des données csv
     *
     * @param  string $file path file
     * @return array
     */
    protected function getDatas($file)
    {
        // Conversion du CSV to PHP Array
        $data = ConvertCsvToArray::convert($file, ';');

        return $data;
    }

    /**
    * Suppression des données
    * @return [type] [description]
    */
    public function deleteData($className)
    {
        $classMetaData = $this->em->getClassMetadata($className);

        $connection = $this->em->getConnection();
        $connection->beginTransaction();
        try {
            $connection->query('DELETE FROM ' . $classMetaData->getTableName());
            $connection->commit();
        } catch (\Exception $e) {
            echo $e->getMessage();
            $connection->rollback();
        }

        // Reinitialisation de l'autoincrement
        $connection->beginTransaction();
        try {
            $connection->query('ALTER TABLE '.$classMetaData->getTableName().' AUTO_INCREMENT = 1');
            $connection->commit();
        } catch (\Exception $e) {
            echo $e->getMessage();
            $connection->rollback();
        }
    }

    /**
     * Chargement des classes de situation de travail
     *
     */
    protected function loadClassesSituationsTravails()
    {
        $now = new \DateTime();
        $this->logInfo('Chargement des classes de situation de travail | '. $now->format('d-m-Y G:i:s'));

        // Initilisation
        $file      = $this->basepath.'/maeva_classe_situation_travail.csv';
        $datas     = $this->getDatas($file);
        $size      = count($datas);
        $batchSize = 20;
        $i         = 1;

        // Suppression des données avant de commencer
        if (0 < $size) {
            $this->deleteData('WundermanEpreventionBundle:ClasseSituationTravail');
        }

        // Starting progress
        $progress = $this->getProgressBar($size);
        $progress->start();

        // Processing on each row of data
        foreach($datas as $row) {
            $entity = new ClasseSituationTravail();
            $entity->setRemoteId($row['contentobject_id']);
            $entity->setCode(HelperText::mb_strtocleanCustom($row['code'], $this->escapeCars));
            $entity->setTitre(HelperText::mb_strtocleanCustom($row['titre'], $this->escapeCars));
            $this->em->persist($entity);

            // Each 20 users persisted we flush everything
            if (($i % $batchSize) === 0) {
                $this->em->flush();
                $this->em->clear();

                $progress->advance($batchSize);
            }

            $i++;
        }

        $this->em->flush();
        $this->em->clear();

        $progress->finish();
        $this->output->writeln('');
    }

    /**
     * Chargement des situations de travail
     *
     */
    protected function loadSituationsTravails()
    {
        $now = new \DateTime();
        $this->logInfo('Chargement des situations de travail | '. $now->format('d-m-Y G:i:s'));

        // Initilisation
        $file      = $this->basepath.'/maeva_situation_travail.csv';
        $datas     = $this->getDatas($file);
        $size      = count($datas);
        $batchSize = 20;
        $i         = 1;

        // Suppression des données avant de commencer
        if (0 < $size) {
            $this->deleteData('WundermanEpreventionBundle:SituationTravail');
        }

        // Starting progress
        $progress = $this->getProgressBar($size);
        $progress->start();

        // Processing on each row of data
        foreach($datas as $row) {
            $entity = new SituationTravail();
            $entity->setRemoteId($row['contentobject_id']);
            $entity->setCode(HelperText::mb_strtocleanCustom($row['code'], $this->escapeCars));
            $entity->setTitre(HelperText::mb_strtocleanCustom($row['titre'], $this->escapeCars));
            $entity->setActif($row['actif']);
            $classe = $this->em->getRepository('WundermanEpreventionBundle:ClasseSituationTravail')->findOneBy(array('remoteId' => $row['classe_situation_travail_contentobject_id']));
            $entity->setClasseSituationTravail($classe);
            $this->em->persist($entity);

            // Each 20 users persisted we flush everything
            if (($i % $batchSize) === 0) {
                $this->em->flush();
                $this->em->clear();

                $progress->advance($batchSize);
            }

            $i++;
        }

        $this->em->flush();
        $this->em->clear();

        $progress->finish();
        $this->output->writeln('');
    }

    /**
     * Chargement des risques
     *
     */
    protected function loadRisques()
    {
        $now = new \DateTime();
        $this->logInfo('Chargement des risques | '. $now->format('d-m-Y G:i:s'));

        // Initilisation
        $file      = $this->basepath.'/maeva_dangers.csv';
        $datas     = $this->getDatas($file);
        $size      = count($datas);
        $batchSize = 20;
        $i         = 1;

        // Suppression des données avant de commencer
        if (0 < $size) {
            $this->deleteData('WundermanEpreventionBundle:Risque');
        }

        // Starting progress
        $progress = $this->getProgressBar($size);
        $progress->start();

        // Processing on each row of data
        foreach($datas as $row) {
            $entity = new Risque();
            $entity->setRemoteId($row['contentobject_id']);
            $entity->setCode(HelperText::mb_strtocleanCustom($row['code'], $this->escapeCars));
            $entity->setTitre(HelperText::mb_strtocleanCustom($row['titre'], $this->escapeCars));
            $entity->setType($this->risqueTypes[$row['type']]);
            $entity->setActif($row['actif']);
            $this->em->persist($entity);

            // Each 20 users persisted we flush everything
            if (($i % $batchSize) === 0) {
                $this->em->flush();
                $this->em->clear();

                $progress->advance($batchSize);
            }

            $i++;
        }

        $this->em->flush();
        $this->em->clear();

        $progress->finish();
        $this->output->writeln('');
    }

    /**
     * Chargement des Binomes Situation travail vs Risque
     *
     */
    protected function loadBinomesSituationsTravailsRisques()
    {
        $now = new \DateTime();
        $this->logInfo('Chargement des Binomes situation de travail | '. $now->format('d-m-Y G:i:s'));

        // Initilisation
        $file      = $this->basepath.'/maeva_binome_situation_travail.csv';
        $datas     = $this->getDatas($file);
        $size      = count($datas);
        $batchSize = 20;
        $i         = 1;

        // Suppression des données avant de commencer
        if (0 < $size) {
            $this->deleteData('WundermanEpreventionBundle:BinomeSituationTravailRisque');
        }

        // Starting progress
        $progress = $this->getProgressBar($size);
        $progress->start();

        // Processing on each row of data
        foreach($datas as $row) {
            $entity = new BinomeSituationTravailRisque();
            $entity->setRemoteId($row['contentobject_id']);
            $entity->setCode(HelperText::mb_strtocleanCustom($row['code'], $this->escapeCars));
            $entity->setNiveauDanger($row['niveau_danger']);
            $situation = $this->em->getRepository('WundermanEpreventionBundle:SituationTravail')->findOneBy(array('remoteId' => $row['situation_travail_contentobject_id']));
            $entity->setSituationTravail($situation);
            $risque = $this->em->getRepository('WundermanEpreventionBundle:Risque')->findOneBy(array('remoteId' => $row['danger_contentobject_id']));
            $entity->setRisque($risque);
            $this->em->persist($entity);

            // Each 20 users persisted we flush everything
            if (($i % $batchSize) === 0) {
                $this->em->flush();
                $this->em->clear();

                $progress->advance($batchSize);
            }

            $i++;
        }

        $this->em->flush();
        $this->em->clear();

        $progress->finish();
        $this->output->writeln('');
    }

    /**
     * Chargement des Phases
     *
     */
    protected function loadPhases()
    {
        $now = new \DateTime();
        $this->logInfo('Chargement des Phases | '. $now->format('d-m-Y G:i:s'));

        // Initilisation
        $file      = $this->basepath.'/maeva_phase_travail.csv';
        $datas     = $this->getDatas($file);
        $size      = count($datas);
        $batchSize = 20;
        $i         = 1;

        // Suppression des données avant de commencer
        if (0 < $size) {
            $this->deleteData('WundermanEpreventionBundle:Phase');
        }

        // Starting progress
        $progress = $this->getProgressBar($size);
        $progress->start();

        // Processing on each row of data
        foreach($datas as $row) {
            $entity = new Phase();
            $entity->setRemoteId($row['contentobject_id']);
            $entity->setCode(HelperText::mb_strtocleanCustom($row['code'], $this->escapeCars));
            $entity->setTitre(HelperText::mb_strtocleanCustom($row['titre'], $this->escapeCars));
            $this->em->persist($entity);

            // Each 20 users persisted we flush everything
            if (($i % $batchSize) === 0) {
                $this->em->flush();
                $this->em->clear();

                $progress->advance($batchSize);
            }

            $i++;
        }

        $this->em->flush();
        $this->em->clear();

        $progress->finish();
        $this->output->writeln('');
    }

    /**
     * Chargement des Activités
     *
     */
    protected function loadActivites()
    {
        $now = new \DateTime();
        $this->logInfo('Chargement des Activités | '. $now->format('d-m-Y G:i:s'));

        // Initilisation
        $file      = $this->basepath.'/maeva_activite.csv';
        $datas     = $this->getDatas($file);
        $size      = count($datas);
        $batchSize = 20;
        $i         = 1;

        // Suppression des données avant de commencer
        if (0 < $size) {
            $this->deleteData('WundermanEpreventionBundle:Activite');
        }

        // Starting progress
        $progress = $this->getProgressBar($size);
        $progress->start();

        // Processing on each row of data
        foreach($datas as $row) {
            $entity = new Activite();
            $entity->setRemoteId($row['contentobject_id']);
            $entity->setCode(HelperText::mb_strtocleanCustom($row['code'], $this->escapeCars));
            if ( 'A50' == $row['code']) {
                $entity->setShortName("Amiante SS4");
            }
            if ( 'A51' == $row['code']) {
                $entity->setShortName("Amiante SS3");
            }
            $entity->setTitre(HelperText::mb_strtocleanCustom($row['titre'], $this->escapeCars));
            $this->em->persist($entity);

            // Each 20 users persisted we flush everything
            if (($i % $batchSize) === 0) {
                $this->em->flush();
                $this->em->clear();

                $progress->advance($batchSize);
            }

            $i++;
        }

        $this->em->flush();
        $this->em->clear();

        $progress->finish();
        $this->output->writeln('');
    }

    /**
     * Chargement des Metiers
     *
     */
    protected function loadMetiers()
    {
        // Initilisation
        $file      = $this->basepath.'/maeva_metier.csv';
        $datas     = $this->getDatas($file);
        $size      = count($datas);
        $batchSize = 20;
        $i         = 1;

        // Processing on each row of data
        foreach($datas as $row) {
            $entity = new Metier();
            $entity->setRemoteId($row['contentobject_id']);
            $entity->setCode(HelperText::mb_strtocleanCustom($row['code'], $this->escapeCars));
            $entity->setTitre(HelperText::mb_strtocleanCustom($row['titre'], $this->escapeCars));
            $this->em->persist($entity);

            // Each 20 users persisted we flush everything
            if (($i % $batchSize) === 0) {
                $this->em->flush();
                $this->em->clear();
            }
            $i++;
        }

        $this->em->flush();
        $this->em->clear();

        echo "Metier loaded";
    }

    /**
     * Chargement des Facteurs Pénbilités
     *
     */
    protected function loadFacteursPenibilites()
    {
        $now = new \DateTime();
        $this->logInfo('Chargement des Facteurs de pénibilité | '. $now->format('d-m-Y G:i:s'));

        // Initilisation
        $file      = $this->basepath.'/maeva_facteur_penibilite.csv';
        $datas     = $this->getDatas($file);
        $size      = count($datas);
        $batchSize = 20;
        $i         = 1;

        // Suppression des données avant de commencer
        if (0 < $size) {
            $this->deleteData('WundermanEpreventionBundle:FacteurPenibilite');
        }

        // Starting progress
        $progress = $this->getProgressBar($size);
        $progress->start();

        // Processing on each row of data
        foreach($datas as $row) {
            $entity = new FacteurPenibilite();
            $entity->setRemoteId($row['contentobject_id']);
            $entity->setTitre(HelperText::mb_strtocleanCustom($row['titre'], $this->escapeCars));
            $entity->setManutentionsManuelles($row['manutentions_manuelles']);
            $entity->setAcd($row['acd']);
            $entity->setBruit($row['bruit']);
            $entity->setGestesRepetes($row['gestes_repetes']);
            $entity->setTemperaturesExtremes($row['temperatures_extremes']);
            $entity->setVibrations($row['vibrations']);
            $entity->setPosturesPenibles($row['postures_penibles']);
            $entity->setAmbiancesClimatiques($row['ambiances_climatique']);
            $metier = $this->em->getRepository('WundermanEpreventionBundle:Metier')->findOneBy(array('remoteId' => $row['metier_contentobject_id']));
            $entity->setMetier($metier);
            $binome = $this->em->getRepository('WundermanEpreventionBundle:BinomeSituationTravailRisque')->findOneBy(array('remoteId' => $row['binome_contentobject_id']));
            $entity->setBinome($binome);
            $phase = $this->em->getRepository('WundermanEpreventionBundle:Phase')->findOneBy(array('remoteId' => $row['phase_contentobject_id']));
            $entity->setPhase($phase);

            $this->em->persist($entity);

            // Each 20 users persisted we flush everything
            if (($i % $batchSize) === 0) {
                $this->em->flush();
                $this->em->clear();

                $progress->advance($batchSize);
            }

            $i++;
        }

        $this->em->flush();
        $this->em->clear();

        $progress->finish();
        $this->output->writeln('');
    }

    /**
     * Chargement de l'association entre les activités et les phases de travail
     *
     */
    protected function loadActivitesPhasesTravails()
    {
        $now = new \DateTime();
        $this->logInfo('Chargement de l\'association entre les activités et les phases de travail | '. $now->format('d-m-Y G:i:s'));

        // Initilisation
        $file      = $this->basepath.'/maeva_activite_phase.csv';
        $datas     = $this->getDatas($file);
        $size      = count($datas);
        $batchSize = 20;
        $i         = 1;
        $activites = $this->em->getRepository('WundermanEpreventionBundle:Activite')->findAll();

        // Starting progress
        $progress = $this->getProgressBar($size);
        $progress->start();

        // Processing on each row of data
        foreach($activites as $activite) {
            // Filtre les datas en fonction du remoteId de l'activité en cours
            $datasFiltred = HelperArray::array_filter_custom($datas, 'activite_contentobject_id', $activite->getRemoteId());

            $phases = new ArrayCollection();
            foreach ($datasFiltred as $row) {
                $phase = $this->em->getRepository('WundermanEpreventionBundle:Phase')->findOneBy(array('remoteId' => $row['phase_travail_contentobject_id']));

                $phases->add($phase);
            }

            $activite->setPhases($phases);
            $this->em->persist($activite);

             // Each 20 users persisted we flush everything
            if (($i % $batchSize) === 0) {
                $this->em->flush();

                $progress->advance($batchSize);
            }

            $i++;
        }

        $this->em->flush();

        $progress->finish();
        $this->output->writeln('');
    }

    /**
     * Chargement de l'association entre les metiers et les activités
     *
     */
    protected function loadMetiersActivites()
    {
        $now = new \DateTime();
        $this->logInfo('Chargement de l\'association entre les métiers et les activités | '. $now->format('d-m-Y G:i:s'));

        // Initilisation
        $file      = $this->basepath.'/maeva_metier_activite.csv';
        $datas     = $this->getDatas($file);
        $size      = count($datas);
        $batchSize = 20;
        $i         = 1;
        $metiers = $this->em->getRepository('WundermanEpreventionBundle:Metier')->findAll();

        // Starting progress
        $progress = $this->getProgressBar($size);
        $progress->start();

        // Processing on each row of data
        foreach($metiers as $metier) {
            // Filtre les datas en fonction du remoteId du metier en cours
            $datasFiltred = HelperArray::array_filter_custom($datas, 'metier_contentobject_id', $metier->getRemoteId());

            $activites = new ArrayCollection();
            foreach ($datasFiltred as $row) {
                $activite = $this->em->getRepository('WundermanEpreventionBundle:Activite')->findOneBy(array('remoteId' => $row['activite_contentobject_id']));

                $activites->add($activite);
            }

            $metier->setActivites($activites);
            $this->em->persist($metier);

             // Each 20 users persisted we flush everything
            if (($i % $batchSize) === 0) {
                $this->em->flush();

                $progress->advance($batchSize);
            }

            $i++;
        }

        $this->em->flush();

        $progress->finish();
        $this->output->writeln('');
    }

    /**
     * Chargement de l'association entre les metiers et les situations de travail
     *
     */
    protected function loadMetiersSituationsTravails()
    {
        $now = new \DateTime();
        $this->logInfo('Chargement de l\'association entre les metiers et les situations de travail | '. $now->format('d-m-Y G:i:s'));

        // Initilisation
        $file      = $this->basepath.'/maeva_metier_situation.csv';
        $datas     = $this->getDatas($file);
        $size      = count($datas);
        $batchSize = 20;
        $i         = 1;
        $metiers = $this->em->getRepository('WundermanEpreventionBundle:Metier')->findAll();

        // Starting progress
        $progress = $this->getProgressBar($size);
        $progress->start();

        // Processing on each row of data
        foreach($metiers as $metier) {
            // Filtre les datas en fonction du remoteId du metier en cours
            $datasFiltred = HelperArray::array_filter_custom($datas, 'metier_contentobject_id', $metier->getRemoteId());

            $situations = new ArrayCollection();
            foreach ($datasFiltred as $row) {
                $situation = $this->em->getRepository('WundermanEpreventionBundle:SituationTravail')->findOneBy(array('remoteId' => $row['situation_travail_contentobject_id']));

                $situations->add($situation);
            }

            if (0 < count($situations)) {
                $metier->setSituationsTravails($situations);
                $this->em->persist($metier);

                 // Each 20 users persisted we flush everything
                if (($i % $batchSize) === 0) {
                    $this->em->flush();

                    $progress->advance($batchSize);
                }
            }

            $i++;

        }

        $this->em->flush();

        $progress->finish();
        $this->output->writeln('');
    }

    /**
     * Chargement de l'association entre les phases et les binomes des situations de travail
     *
     */
    protected function loadPhasesTravailsBinomesSituationsTravailsRisques()
    {
        $now = new \DateTime();
        $this->logInfo('Chargement de l\'association entre les phases et les binomes des situations de travail | '. $now->format('d-m-Y G:i:s'));

        // Initilisation
        $file      = $this->basepath.'/maeva_phase_travail_binome_situation_travail.csv';
        $datas     = $this->getDatas($file);
        $size      = count($datas);
        $batchSize = 20;
        $i         = 1;
        $phases = $this->em->getRepository('WundermanEpreventionBundle:Phase')->findAll();

        // Starting progress
        $progress = $this->getProgressBar($size);
        $progress->start();

        // Processing on each row of data
        foreach($datas as $row) {
            $binome = $this->em->getRepository('WundermanEpreventionBundle:BinomeSituationTravailRisque')->findOneBy(array('remoteId' => $row['binome_situation_travail_contentobject_id']));

            if ($binome) {
                $phase = $this->em->getRepository('WundermanEpreventionBundle:Phase')->findOneBy(array('remoteId' => $row['phase_travail_contentobject_id']));

                if ($phase) {
                    $phase->addBinome($binome);
                    $this->em->persist($phase);
                }
            }

             // Each 20 users persisted we flush everything
            if (($i % $batchSize) === 0) {
                $this->em->flush();

                $progress->advance($batchSize);
            }

            $i++;
        }

        $this->em->flush();

        $progress->finish();
        $this->output->writeln('');
    }

}