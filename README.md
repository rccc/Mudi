Mudi
====

Mudi - Application contenant quelques outils de tests utiles lors de la création de petits projets web

Installation 

* Cloner le dépôt
* Se déplacer à la racine et installer "composer"
* effectuer la commande : php composer.phar install 




Mudi\Resource

     +
     |
     |
     v

Mudi\Command / Silex\Controler  +-------->      Mudi\Event($service, $resource, $results)

     +           ^                                         +
     |           |                                         |
     |           +-----------------+                       |
     |                             |                       |
     v                             +                       |
                                                           +---->   Mudi\ScoringSubscriber
Mudi\ProxyService  +------->   Mudi\Collection

     +       ^
     |       |
     |       |
     |       +--------------------+
     v                            |
                                  +

 Service   +------------>    Mudi\Result





 Une Resource correspond à une archive, un fichier ou un dossier.

 Une commande ou un controlleur prend en charge cette resource et instancie un ProxyService

 qui reçoit la ressource en paramètre et sollicite le service.

 En retour, le service retourne une instance de Mudi\Result qui sera ajoutée à une

 instance de Mudi\Collection ( au niveau du proxyService ).

 A ce momemt, un evenement est alors créer dans lequel on injecte une référence à la

 resource, au service et aux résultats ( Mudi\Collection qui elle même contient des

 instance de Mudi\Result.


 La classe Mudi\Subscriber capture l'évenement et exploite les résultats.

 Des templates twig sont sollicités pour affichage en console ou en HTML.
